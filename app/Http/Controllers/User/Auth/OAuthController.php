<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\AddAppleEmailRequest;
use App\Http\Requests\User\Auth\OAuthRequest;
use App\Http\Resources\User\ProfileResource;
use App\Repositories\FcmTokenRepository;
use App\Repositories\UserRepository;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Illuminate\Support\Facades\DB;
use App\Services\Admin\UserService;

class OAuthController extends Controller
{
    public function __construct(
        private UserRepository $userRepository,
        private FcmTokenRepository $fcmTokenRepository,
        private UserService $userService
    ) {}

    public function handle(OAuthRequest $request, string $provider)
    {
        $socialUser = $this->getSocialUser($provider, $request->token);

        if ($provider == 'apple') {
            return $this->handleApple($socialUser, $request->email);
        }

        return $this->handleGeneric($socialUser, $provider);
    }

    private function getSocialUser(string $provider, string $token)
    {
        return Socialite::driver($provider)->stateless()->userFromToken($token);
    }

    /* ================================
       GOOGLE / FACEBOOK / OTHERS
       ================================ */
    private function handleGeneric($socialUser, string $provider)
    {
        $user = $this->userRepository->getByProviderId($provider, $socialUser->getId());

        if ($user) {
            return $this->login($user);
        }

        return $this->register($socialUser, $provider, $socialUser->getEmail());
    }

    /* ================================
       APPLE
       ================================ */
    private function handleApple($socialUser)
    {
        $user = $this->userRepository->getByProviderId('apple', $socialUser->getId());

        if ($user) {
            return $this->login($user);
        }

        return successMessage("add your contact email", 220);
    }

    private function login($user)
    {
        if (!$user->is_active) {
            throw new BadRequestHttpException(__('your account is disabled'));
        }

        $token = $user->createToken('api-token');

        // Update user locale
        $user->update([
            'app_locale' => app()->getLocale(),
        ]);

        // Save FCM token if provided
        if (request('fcm_token')) {
            $this->fcmTokenRepository->createOrUpdate($user, request('device_id'), request('fcm_token'), $token->accessToken->id);
        }

        return success([
            'token' => $token->plainTextToken,
            'app_locale' => $user->app_locale,
            'user' => ProfileResource::make($user),
        ]);
    }

    private function register($socialUser, string $provider, string $email)
    {
        return DB::transaction(function () use ($socialUser, $provider, $email) {

            // provider table → relay email ONLY for Apple
            $providerData = [
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ];

            if ($provider == 'apple') {
                $providerData['apple_relay_email'] = $socialUser->getEmail();
            }

            $user = $this->userRepository->getByEmail($email);

            if ($user) {
                $user->socialProviders()->create($providerData);
                return $this->login($user);
            }

            // users table → contact email
            $user = $this->userRepository->create([
                'name' => $socialUser->getName(),
                'email' => $email,
                'uuid'       => $this->userService->generateUuid(),
                'app_locale' => app()->getLocale(),
            ]);

            // avatar
            if ($socialUser->getAvatar()) {
                $user->addMediaFromUrl($socialUser->getAvatar())->toMediaCollection();
            }

            $user->socialProviders()->create($providerData);

            $token = $user->createToken('api-token');

            // Save FCM token if provided
            if (request('fcm_token')) {
                $this->fcmTokenRepository->createOrUpdate($user, request('device_id'), request('fcm_token'), $token->accessToken->id);
            }

            return success([
                'token' => $token->plainTextToken,
                'app_locale' => app()->getLocale(),
                'user' => ProfileResource::make($user),
            ]);
        });
    }

    public function addAppleContactEmail(AddAppleEmailRequest $request)
    {
        $socialUser = $this->getSocialUser('apple', $request->token);

        $user = $this->userRepository->getByProviderId('apple', $socialUser->getId());

        if ($user) {
            throw new BadRequestHttpException(__('user already exists'));
        }

        return $this->register($socialUser, 'apple', $request->email);
    }
}
