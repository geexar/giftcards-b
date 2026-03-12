<?php

namespace App\Http\Controllers\User\Auth;

use App\Enums\OtpType;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\RegistrationRequest;
use App\Http\Resources\User\ProfileResource;
use App\Models\User;
use App\Repositories\CartRepository;
use App\Repositories\FcmTokenRepository;
use App\Repositories\OtpRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use App\Services\Admin\UserService;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RegistrationController extends Controller
{
    private const REGISTRATION_ATTEMPTS_LIMIT = 5;
    private const REGISTRATION_DECAY_SECONDS  = 60;

    public function __construct(
        private UserService $userService,
        private UserRepository $userRepository,
        private OtpRepository $otpRepository,
        private CartRepository $cartRepository,
        private FcmTokenRepository $fcmTokenRepository
    ) {}

    public function store(RegistrationRequest $request)
    {
        // 1. Rate limit check
        $this->throttleRegistrationAttempt($request->email);

        // 2. Verify OTP
        $this->verifyOtpOrFail($request->email, $request->otp);

        // 3. Prepare data for user creation
        $data = $request->validated();

        if (!empty($data['phone'])) {
            $data['phone'] = normalizePhoneNumber($request->phone);
        }

        $data['app_locale'] = app()->getLocale();
        $data['uuid']       = $this->userService->generateUuid();

        // 4. Create user, token, and related DB records within a transaction
        try {
            DB::beginTransaction();

            $user = $this->userRepository->create($data);

            // Create API token
            $token = $user->createToken('api-token');

            // Delete used OTPs
            $this->otpRepository->deleteAll('user', $user->email, OtpType::EMAIL_VERIFICATION);

            // Merge guest cart into user account
            $this->mergeGuestCart($user);

            // Save FCM token if provided
            if ($request->fcm_token) {
                $this->fcmTokenRepository->createOrUpdate($user, $request->device_id, $request->fcm_token, $token->accessToken->id);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        // 5. Return success response
        return success([
            'token'      => $token->plainTextToken,
            'app_locale' => app()->getLocale(),
            'user'       => ProfileResource::make($user)
        ]);
    }


    private function throttleRegistrationAttempt(string $email): void
    {
        $key = "registration-attempt:{$email}";

        if (RateLimiter::tooManyAttempts($key, self::REGISTRATION_ATTEMPTS_LIMIT)) {
            $remainingTime = formatSecondsToMinutesTime(RateLimiter::availableIn($key));
            throw new BadRequestHttpException(
                __('too many attempts: retry after :time minutes', ['time' => $remainingTime])
            );
        }

        RateLimiter::hit($key, self::REGISTRATION_DECAY_SECONDS);
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

    public function mergeGuestCart(User $user)
    {
        $guestToken = request()->header('X-Guest-Token');

        if (!$guestToken) {
            return;
        }

        $this->cartRepository->mergeGuestCart($user, $guestToken);
    }
}
