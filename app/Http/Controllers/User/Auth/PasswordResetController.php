<?php

namespace App\Http\Controllers\User\Auth;

use App\Enums\OtpType;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\ResetPasswordRequest;
use App\Http\Requests\Admin\Auth\ConfirmOtpRequest;
use App\Http\Requests\Admin\Auth\SendOtpRequest;
use App\Mail\PasswordResetMail;
use App\Repositories\UserRepository;
use App\Repositories\OtpRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PasswordResetController extends Controller
{
    /** ------------------------
     *  Reset Limits
     * ------------------------
     */
    private const SEND_RESET_CODE_LIMIT = 3;
    private const CONFIRM_RESET_CODE_LIMIT = 3;
    private const RESET_PASSWORD_LIMIT = 3;

    /** ------------------------
     *  Timing
     * ------------------------
     */
    private const ATTEMPT_LOCK_SECONDS = 120; // 2 min
    private const RESET_CODE_EXPIRY_HOURS = 6;
    private const RESET_CODE_RESEND_WAIT_SECONDS = 60;  // 1 min
    private const RESET_CODE_GLOBAL_BLOCK_HOURS = 12;
    private const RESET_CODE_GLOBAL_BLOCK_SECONDS = self::RESET_CODE_GLOBAL_BLOCK_HOURS * 3600;

    public function __construct(
        private UserRepository $userRepository,
        private OtpRepository $otpRepository,
    ) {}

    /**
     * STEP 1 — Send reset code
     */
    public function sendResetCode(SendOtpRequest $request)
    {
        $email = strtolower($request->email);
        $user = $this->userRepository->getByEmail($email);

        if (! $user) {
            throw new NotFoundHttpException(__('user not found'));
        }

        $key = "send-reset-code-user:{$user->id}";
        $cooldownKey = "reset-code-cooldown-user:{$user->id}";

        // Global 12-hour limit
        if (RateLimiter::tooManyAttempts($key, self::SEND_RESET_CODE_LIMIT)) {
            $remaining = formatSecondsToHoursTime(RateLimiter::availableIn($key));
            throw new BadRequestHttpException(__('limit reached: retry after :time hours', ['time' => $remaining]));
        }

        // 1-minute cooldown
        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $remaining = formatSecondsToMinutesTime(RateLimiter::availableIn($cooldownKey));
            throw new BadRequestHttpException(__('retry after :time minutes', ['time' => $remaining]));
        }


        DB::transaction(function () use ($user, $request, $key, $cooldownKey) {
            $code = generateOtp();

            // Store OTP
            $this->otpRepository->create([
                'user_type' => 'user',
                'email' => $user->email,
                'type' => OtpType::PASSWORD_RESET->value,
                'code' => $code,
                'expires_at' => now()->addHours(self::RESET_CODE_EXPIRY_HOURS),
            ]);

            Mail::to($request->email)->send(new PasswordResetMail($code));

            RateLimiter::hit($key, self::RESET_CODE_GLOBAL_BLOCK_SECONDS);
            RateLimiter::hit($cooldownKey, self::RESET_CODE_RESEND_WAIT_SECONDS);
        });


        return success(true);
    }

    /**
     * STEP 1-B — Check if allowed to send code
     */
    public function allowedToSendCode(SendOtpRequest $request)
    {
        $email = strtolower($request->email);

        // check for email and phone
        $user = $this->userRepository->getByEmail($email);

        if (! $user) {
            throw new NotFoundHttpException('user not found');
        }

        $key = "send-reset-code-user:{$user->id}";
        $cooldownKey = "reset-code-cooldown-user:{$user->id}";

        $globallyBlocked = RateLimiter::tooManyAttempts($key, self::SEND_RESET_CODE_LIMIT);
        $cooldownActive = RateLimiter::tooManyAttempts($cooldownKey, 1);

        return success([
            'is_allowed' => !($globallyBlocked || $cooldownActive)
        ]);
    }

    /**
     * STEP 2 — Confirm reset code
     */
    public function confirmResetCode(ConfirmOtpRequest $request)
    {
        $email = strtolower($request->email);

        $user = $this->userRepository->getByEmail($email);

        if (! $user) {
            throw new NotFoundHttpException('user not found');
        }

        $key = "confirm-reset-user:{$user->id}";

        if (RateLimiter::tooManyAttempts($key, self::CONFIRM_RESET_CODE_LIMIT)) {
            $remaining = formatSecondsToMinutesTime(RateLimiter::availableIn($key));
            throw new BadRequestHttpException(__('too many attempts: retry after :time minutes', ['time' => $remaining]));
        }

        RateLimiter::hit($key, self::ATTEMPT_LOCK_SECONDS);

        $otp = $this->otpRepository->getLatest('user', $user->email, OtpType::PASSWORD_RESET);

        if (! $otp || $otp->code != $request->otp) {
            throw new BadRequestHttpException(__('invalid otp'));
        }

        if ($otp->expires_at < now()) {
            throw new BadRequestHttpException(__('expired otp'));
        }

        return success(true);
    }

    /**
     * STEP 3 — Reset password
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $email = strtolower($request->email);

        $user = $this->userRepository->getByEmail($email);

        if (! $user) {
            throw new NotFoundHttpException('user not found');
        }

        $key = "reset-password-user:{$user->id}";

        if (RateLimiter::tooManyAttempts($key, self::RESET_PASSWORD_LIMIT)) {
            $remaining = formatSecondsToMinutesTime(RateLimiter::availableIn($key));
            throw new BadRequestHttpException(__('too many attempts: retry after :time minutes', ['time' => $remaining]));
        }

        RateLimiter::hit($key, self::ATTEMPT_LOCK_SECONDS);

        $otp = $this->otpRepository->getLatest('user', $user->email, OtpType::PASSWORD_RESET);

        if (! $otp || $otp->code != $request->otp) {
            throw new BadRequestHttpException(__('invalid otp'));
        }

        if ($otp->expires_at < now()) {
            throw new BadRequestHttpException(__('expired otp'));
        }

        DB::transaction(function () use ($user, $request) {
            $user->update(['password' => bcrypt($request->password)]);
            $this->otpRepository->deleteAll('user', $user->email, OtpType::PASSWORD_RESET);
        });

        return success(true);
    }
}
