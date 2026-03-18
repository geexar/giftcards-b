<?php

namespace App\Http\Controllers\User\Auth;

use App\Enums\OtpType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\ConfirmOtpRequest;
use App\Http\Requests\Admin\Auth\SendOtpRequest;
use App\Http\Requests\User\SendUpdateEmailOtpRequest;
use App\Mail\EmailVerificationMail;
use App\Repositories\UserRepository;
use App\Repositories\OtpRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class EmailVerificaionController extends Controller
{
    /** ------------------------
     *  Limits
     * ------------------------ */
    private const SEND_CODE_LIMIT = 3;
    private const VERIFY_CODE_LIMIT = 3;

    /** ------------------------
     *  Timing
     * ------------------------ */
    private const ATTEMPT_LOCK_SECONDS = 120; // 2 minutes
    private const CODE_EXPIRY_HOURS = 6;
    private const CODE_RESEND_COOLDOWN = 60; // 1 minute
    private const GLOBAL_BLOCK_HOURS = 12;
    private const GLOBAL_BLOCK_SECONDS = self::GLOBAL_BLOCK_HOURS * 3600;

    public function __construct(
        private UserRepository $userRepository,
        private OtpRepository $otpRepository,
    ) {}

    /**
     * Send verification code for new user
     */
    public function sendVerificationCode(SendOtpRequest $request)
    {
        $email = strtolower($request->email);

        $user = $this->userRepository->getByEmail($email);

        if ($user) {
            throw new BadRequestHttpException(__('email already verified'));
        }

        $key = "send-email-verify:{$email}";
        $cooldownKey = "email-verify-cooldown:{$email}";

        // global 12-hour limit
        if (RateLimiter::tooManyAttempts($key, self::SEND_CODE_LIMIT)) {
            $remaining = formatSecondsToHoursTime(RateLimiter::availableIn($key));
            throw new BadRequestHttpException(__('limit reached: retry after :time hours', ['time' => $remaining]));
        }

        // 1-minute cooldown
        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $remaining = formatSecondsToMinutesTime(RateLimiter::availableIn($cooldownKey));
            throw new BadRequestHttpException(__('retry after :time minutes', ['time' => $remaining]));
        }

        DB::transaction(function () use ($email, $key, $cooldownKey) {
            $code = generateOtp();

            // store OTP
            $this->otpRepository->create([
                'user_type' => 'user',
                'email' => $email,
                'type' => OtpType::EMAIL_VERIFICATION->value,
                'code' => $code,
                'expires_at' => now()->addHours(self::CODE_EXPIRY_HOURS),
            ]);

            // send email
            Mail::to($email)->send(new EmailVerificationMail($code));

            RateLimiter::hit($key, self::GLOBAL_BLOCK_SECONDS);
            RateLimiter::hit($cooldownKey, self::CODE_RESEND_COOLDOWN);
        });

        return success(true);
    }

    /**
     * Send verification code for updating user email
     */
    public function sendUpdateEmailVerificationCode(SendUpdateEmailOtpRequest $request)
    {
        $authUser = auth('user')->user();
        $email = strtolower($request->email);

        // password rate limit
        $passwordKey = "email-verify-password:{$authUser->id}";

        if (RateLimiter::tooManyAttempts($passwordKey, 5)) {
            $remaining = formatSecondsToMinutesTime(
                RateLimiter::availableIn($passwordKey)
            );

            throw new BadRequestHttpException(
                __('too many password attempts: retry after :time minutes', ['time' => $remaining])
            );
        }

        // verify password
        if (! Hash::check($request->password, $authUser->password)) {
            RateLimiter::hit($passwordKey, self::ATTEMPT_LOCK_SECONDS);
            throw new BadRequestHttpException(__('invalid password'));
        }

        RateLimiter::clear($passwordKey);

        // email already used
        $userWithEmail = $this->userRepository->getByEmail($email);
        if ($userWithEmail) {
            $errorMessage = $userWithEmail->id == $authUser->id ? __('email already verified') : __('email already used');
            throw new BadRequestHttpException($errorMessage);
        }

        $key = "send-email-verify:{$email}-{$authUser->id}";
        $cooldownKey = "email-verify-cooldown:{$email}-{$authUser->id}";

        if (RateLimiter::tooManyAttempts($key, self::SEND_CODE_LIMIT)) {
            $remaining = formatSecondsToHoursTime(
                RateLimiter::availableIn($key)
            );

            throw new BadRequestHttpException(
                __('limit reached: retry after :time hours', ['time' => $remaining])
            );
        }

        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $remaining = formatSecondsToMinutesTime(
                RateLimiter::availableIn($cooldownKey)
            );

            throw new BadRequestHttpException(
                __('retry after :time minutes', ['time' => $remaining])
            );
        }

        DB::transaction(function () use ($email, $key, $cooldownKey) {
            $code = generateOtp();

            $this->otpRepository->create([
                'user_type'  => 'user',
                'email'      => $email,
                'type'       => OtpType::EMAIL_VERIFICATION->value,
                'code'       => $code,
                'expires_at' => now()->addHours(self::CODE_EXPIRY_HOURS),
            ]);

            Mail::to($email)->send(new EmailVerificationMail($code));

            RateLimiter::hit($key, self::GLOBAL_BLOCK_SECONDS);
            RateLimiter::hit($cooldownKey, self::CODE_RESEND_COOLDOWN);
        });

        return success(true);
    }


    /**
     * STEP 1-B — Check if allowed to send verification code
     */
    public function allowedToSendVerificationCode(SendOtpRequest $request)
    {
        $email = strtolower($request->email);

        $user = $this->userRepository->getByEmail($email);

        if ($user) {
            throw new BadRequestHttpException(__('email already verified'));
        }

        $key = "send-email-verify:{$email}";
        $cooldownKey = "email-verify-cooldown:{$email}";

        $globallyBlocked = RateLimiter::tooManyAttempts($key, self::SEND_CODE_LIMIT);
        $cooldownActive = RateLimiter::tooManyAttempts($cooldownKey, 1);

        return success([
            'is_allowed' => !($globallyBlocked || $cooldownActive)
        ]);
    }

    /**
     * STEP 2 — Verify email code
     */
    public function verify(ConfirmOtpRequest $request)
    {
        $email = strtolower($request->email);

        $user = $this->userRepository->getByEmail($email);

        if ($user) {
            throw new BadRequestHttpException(__('email already verified'));
        }

        $key = "verify-email:{$email}";

        if (RateLimiter::tooManyAttempts($key, self::VERIFY_CODE_LIMIT)) {
            $remaining = formatSecondsToMinutesTime(RateLimiter::availableIn($key));
            throw new BadRequestHttpException(__('too many attempts: retry after :time minutes', ['time' => $remaining]));
        }

        RateLimiter::hit($key, self::ATTEMPT_LOCK_SECONDS);

        // get latest OTP
        $otp = $this->otpRepository->getLatest('user', $email, OtpType::EMAIL_VERIFICATION);

        if (! $otp || $otp->code != $request->otp) {
            throw new BadRequestHttpException(__('invalid otp'));
        }

        if ($otp->expires_at < now()) {
            throw new BadRequestHttpException(__('expired otp'));
        }

        return success(true);
    }
}
