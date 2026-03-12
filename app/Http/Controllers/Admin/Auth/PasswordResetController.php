<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Enums\OtpType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\ConfirmOtpRequest;
use App\Http\Requests\Admin\Auth\ResetPasswordRequest;
use App\Http\Requests\Admin\Auth\SendOtpRequest;
use App\Mail\PasswordResetMail;
use App\Repositories\AdminRepository;
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
    private const SEND_RESET_CODE_LIMIT = 3;       // Max 3 send attempts in global block period
    private const CONFIRM_RESET_CODE_LIMIT = 3;    // Max 3 confirm attempts
    private const RESET_PASSWORD_LIMIT = 3;        // Max 3 password reset attempts

    /** ------------------------
     *  Timing Configs
     * ------------------------
     */
    private const ATTEMPT_LOCK_SECONDS = 120; // Lockout time after failed attempt (2 minutes)
    private const RESET_CODE_EXPIRY_HOURS = 6; // OTP validity
    private const RESET_CODE_RESEND_WAIT_SECONDS = 60; // Minimum time between sending OTPs (1 min)
    private const RESET_CODE_GLOBAL_BLOCK_HOURS = 12; // Global block duration in hours for send attempts
    private const RESET_CODE_GLOBAL_BLOCK_SECONDS = self::RESET_CODE_GLOBAL_BLOCK_HOURS * 3600;

    public function __construct(
        private readonly AdminRepository $adminRepository,
        private readonly OtpRepository $otpRepository
    ) {}

    public function sendResetCode(SendOtpRequest $request)
    {
        $admin = $this->adminRepository->getByEmail($request->email);

        if (! $admin) {
            throw new NotFoundHttpException(__('user not found'));
        }

        $key = "send-reset-code-admin:{$admin->id}";       // Global attempt key
        $cooldownKey = "reset-code-cooldown-admin:{$admin->id}"; // Cooldown key for 1 min between sends

        // Global limit check: max sends per 12 hours
        if (RateLimiter::tooManyAttempts($key, self::SEND_RESET_CODE_LIMIT)) {
            $remainingTime = formatSecondsToHoursTime(RateLimiter::availableIn($key));
            throw new BadRequestHttpException(__('limit reached: retry after :time hours', ['time' => $remainingTime]));
        }

        // Cooldown check: minimum time between OTPs
        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            $remainingTime = formatSecondsToMinutesTime(RateLimiter::availableIn($cooldownKey));
            throw new BadRequestHttpException(__('retry after :time seconds', ['time' => $remainingTime]));
        }

        // Generate OTP code
        $code = generateOtp();

        // Save OTP
        $this->otpRepository->create([
            'user_type' => 'admin',
            'email' => $admin->email,
            'type' => OtpType::PASSWORD_RESET->value,
            'code' => $code,
            'expires_at' => now()->addHours(self::RESET_CODE_EXPIRY_HOURS),
        ]);

        // Send OTP via email
        Mail::to($admin->email)->send(new PasswordResetMail($code));

        // Record attempts
        RateLimiter::hit($key, self::RESET_CODE_GLOBAL_BLOCK_SECONDS);
        RateLimiter::hit($cooldownKey, self::RESET_CODE_RESEND_WAIT_SECONDS);

        return success(true);
    }

    public function allowedToSendResetCode(SendOtpRequest $request)
    {
        $admin = $this->adminRepository->getByEmail($request->email);

        if (! $admin) {
            throw new NotFoundHttpException('user not found');
        }

        $key = "send-reset-code-admin:{$admin->id}";
        $cooldownKey = "reset-code-cooldown-admin:{$admin->id}";

        $isGloballyBlocked = RateLimiter::tooManyAttempts($key, self::SEND_RESET_CODE_LIMIT);
        $isInCooldown = RateLimiter::tooManyAttempts($cooldownKey, 1);

        return success([
            'is_allowed' => !($isGloballyBlocked || $isInCooldown)
        ]);
    }

    public function confirmResetCode(ConfirmOtpRequest $request)
    {
        $admin = $this->adminRepository->getByEmail($request->email);

        if (! $admin) {
            throw new NotFoundHttpException('user not found');
        }

        $key = "confirm-reset-admin:{$admin->id}";

        // Rate-limit confirm attempts
        if (RateLimiter::tooManyAttempts($key, self::CONFIRM_RESET_CODE_LIMIT)) {
            $remainingTime = formatSecondsToMinutesTime(RateLimiter::availableIn($key));
            throw new BadRequestHttpException(__('too many attempts: retry after :time minutes', ['time' => $remainingTime]));
        }

        RateLimiter::hit($key, self::ATTEMPT_LOCK_SECONDS);

        $otp = $this->otpRepository->getLatest('admin', $admin->email, OtpType::PASSWORD_RESET);

        if (! $otp || $otp->code != $request->otp) {
            throw new BadRequestHttpException(__('invalid otp'));
        }

        if ($otp->expires_at < now()) {
            throw new BadRequestHttpException(__('expired otp'));
        }

        return success(true);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $admin = $this->adminRepository->getByEmail($request->email);

        if (! $admin) {
            throw new NotFoundHttpException('user not found');
        }

        $key = "reset-password-admin:{$admin->id}";

        // Rate-limit password reset attempts
        if (RateLimiter::tooManyAttempts($key, self::RESET_PASSWORD_LIMIT)) {
            $remainingTime = formatSecondsToMinutesTime(RateLimiter::availableIn($key));
            throw new BadRequestHttpException(__('too many attempts: retry after :time minutes', ['time' => $remainingTime]));
        }

        RateLimiter::hit($key, self::ATTEMPT_LOCK_SECONDS);

        $otp = $this->otpRepository->getLatest('admin', $admin->email, OtpType::PASSWORD_RESET);

        if (! $otp || $otp->code != $request->otp) {
            throw new BadRequestHttpException(__('invalid otp'));
        }

        if ($otp->expires_at < now()) {
            throw new BadRequestHttpException(__('expired otp'));
        }

        // Update password and remove all related OTPs
        DB::transaction(function () use ($admin, $request) {
            $admin->update(['password' => bcrypt($request->password)]);
            $this->otpRepository->deleteAll('admin', $admin->email, OtpType::PASSWORD_RESET);
        });

        return success(true);
    }
}
