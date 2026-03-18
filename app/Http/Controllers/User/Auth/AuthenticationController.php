<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Exceptions\ConstraintException;
use App\Http\Requests\User\Auth\LoginRequest;
use App\Http\Resources\User\ProfileResource;
use App\Repositories\UserRepository;
use App\Repositories\FcmTokenRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthenticationController extends Controller
{
    /** ------------------------
     *  Login Rate Limiting Config
     * ------------------------
     */
    private const LOGIN_ATTEMPTS_LIMIT = 6;        // Max failed login attempts
    private const LOGIN_LOCK_SECONDS = 120;        // 2 minutes lock

    public function __construct(
        private UserRepository $userRepository,
        private FcmTokenRepository $fcmTokenRepository
    ) {}

    public function login(LoginRequest $request)
    {
        $email = strtolower($request->email);

        // 1. Get user
        $user = $this->userRepository->getByEmail($email);

        if (! $user) {
            throw new NotFoundHttpException(__('user not found'));
        }

        // 2. Rate limit
        $key = "login-attempts-user:{$user->id}";
        if (RateLimiter::tooManyAttempts($key, self::LOGIN_ATTEMPTS_LIMIT)) {
            $remainingTime = formatSecondsToMinutesTime(RateLimiter::availableIn($key));
            throw new BadRequestHttpException(__('too many attempts: retry after :time minutes', [
                'time' => $remainingTime
            ]));
        }

        // 3. Record login attempt
        RateLimiter::hit($key, self::LOGIN_LOCK_SECONDS);

        // 4. Verify password
        if (! Hash::check($request->password, $user->password)) {
            throw new BadRequestHttpException(__('auth.failed'));
        }

        // 5. Check active status
        if (! $user->is_active) {
            throw new BadRequestHttpException(__('your account is disabled'));
        }

        // 6. DB operations: token + FCM
        try {
            DB::beginTransaction();

            // Clear attempts after successful login
            RateLimiter::clear($key);

            // Generate API token
            $token = $user->createToken('api-token');

            // Set token expiry if not remember_me
            if (! $request->remember_me) {
                $token->accessToken->expires_at = now()->addDays(14);
                $token->accessToken->save();
            }

            // Update user locale
            $user->update([
                'app_locale' => app()->getLocale(),
            ]);

            // Save FCM token
            if ($request->fcm_token) {
                $this->fcmTokenRepository->createOrUpdate($user, $request->device_id, $request->fcm_token, $token->accessToken->id);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e; // global exception handler will format response
        }

        // 7. Return success
        return success([
            'token'      => $token->plainTextToken,
            'app_locale' => $user->app_locale,
            'user'       => ProfileResource::make($user),
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return success(true);
    }
}
