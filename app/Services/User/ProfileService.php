<?php

namespace App\Services\User;

use App\Enums\OtpType;
use App\Repositories\OtpRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    private const EMAIL_UPDATE_ATTEMPTS_LIMIT = 5;
    private const EMAIL_UPDATE_DECAY_SECONDS  = 60;
    private const PASSWORD_ATTEMPTS_LIMIT = 5;
    private const PASSWORD_ATTEMPTS_DECAY_SECONDS = 300; // 5 minutes

    public function __construct(
        private UserRepository $userRepository,
        private OtpRepository $otpRepository
    ) {}

    public function update(array $data)
    {
        $user = auth('user')->user();

        // dont update name if signed in with social provider
        if ($user->socialProviders->count()) {
            $data['name'] = $user->name;
        }

        if (isset($data['phone'])) {
            $data['phone'] = normalizePhoneNumber($data['phone']);
        }

        DB::transaction(function () use ($user, $data) {
            $this->userRepository->update($user, $data);

            if (isset($data['image'])) {
                $user->addMedia($data['image'])->toMediaCollection();
            }
        });
    }

    public function updateEmail(array $data): void
    {
        $user = auth('user')->user();

        // don't update email if signed in with social provider
        if ($user->socialProviders->whereIn('provider', ['google', 'facebook'])->isNotEmpty()) {
            throw new BadRequestHttpException(
                __('You cannot update email associated with Google or Facebook')
            );
        }

        $email    = $data['email'];
        $otp      = $data['otp'];
        $password = $data['password'];

        /**
         * 1. Password rate limit
         */
        $passwordKey = "update-email-password:{$user->id}";

        if (RateLimiter::tooManyAttempts($passwordKey, self::PASSWORD_ATTEMPTS_LIMIT)) {
            $remaining = formatSecondsToMinutesTime(
                RateLimiter::availableIn($passwordKey)
            );

            throw new BadRequestHttpException(
                __('Too many password attempts. Retry after :time minutes', [
                    'time' => $remaining,
                ])
            );
        }

        RateLimiter::hit($passwordKey, self::PASSWORD_ATTEMPTS_DECAY_SECONDS);

        if (! Hash::check($password, $user->password)) {
            throw new BadRequestHttpException(__('Invalid password'));
        }

        // reset password limiter on success
        RateLimiter::clear($passwordKey);

        /**
         * 2. OTP rate limit
         */
        $otpKey = "verify-email:{$email}";

        if (RateLimiter::tooManyAttempts($otpKey, self::EMAIL_UPDATE_ATTEMPTS_LIMIT)) {
            $remaining = formatSecondsToMinutesTime(
                RateLimiter::availableIn($otpKey)
            );

            throw new BadRequestHttpException(
                __('Too many attempts. Retry after :time minutes', [
                    'time' => $remaining,
                ])
            );
        }

        RateLimiter::hit($otpKey, self::EMAIL_UPDATE_DECAY_SECONDS);

        /**
         * 3. Verify OTP
         */
        $this->verifyOtpOrFail($email, $otp);

        /**
         * 4. Update email
         */
        $this->userRepository->update($user, [
            'email' => $email,
        ]);

        // delete all tokens excpet current tokens
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();
    }

    private function verifyOtpOrFail(string $email, string $otpCode): void
    {
        $otp = $this->otpRepository->getLatest('user', $email, OtpType::EMAIL_VERIFICATION);

        if (!$otp || $otp->code != $otpCode) {
            throw new BadRequestHttpException(__('invalid otp'));
        }

        if ($otp->expires_at < now()) {
            throw new BadRequestHttpException(__('expired otp'));
        }
    }

    public function updatePassword(string $password): void
    {
        // not same old password
        if (Hash::check($password, auth('user')->user()->password)) {
            throw new BadRequestHttpException(__('New password cannot be same as old password'));
        }

        $user = auth('user')->user();
        $this->userRepository->update($user, ['password' => bcrypt($password)]);

        // delete all tokens excpet current tokens
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();
    }

    public function addPassword(string $password): void
    {
        $user = auth('user')->user();

        if ($user->password) {
            throw new BadRequestHttpException('already have a password');
        }

        $this->userRepository->update($user, ['password' => bcrypt($password)]);
    }

    public function updateAppLocale(string $appLocale): void
    {
        $user = auth('user')->user();
        $this->userRepository->update($user, ['app_locale' => $appLocale]);
    }

    public function updateImage(UploadedFile $image)
    {
        $user = auth('user')->user();

        $user->clearMediaCollection();
        $user->addMedia($image)->toMediaCollection();
    }
}
