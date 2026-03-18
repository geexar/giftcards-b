<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Exceptions\ConstraintException;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Http\Resources\Admin\ProfileResource;
use App\Repositories\AdminRepository;
use App\Repositories\FcmTokenRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthenticationController extends Controller
{
    /** ------------------------
     *  Login Rate Limiting Config
     * ------------------------
     */
    private const LOGIN_ATTEMPTS_LIMIT = 5;       // Max failed login attempts before lock
    private const LOGIN_LOCK_SECONDS = 120;       // Lock duration after reaching limit (2 minutes)

    public function __construct(
        private AdminRepository $adminRepository,
        private FcmTokenRepository $fcmTokenRepository
    ) {}

    public function login(LoginRequest $request)
    {
        $email = strtolower($request->email);

        // Get admin
        $admin = $this->adminRepository->getByEmail($email);
        if (! $admin) {
            throw new NotFoundHttpException(__('user not found'));
        }

        // Rate limit
        $key = "login-attempts-admin:{$admin->id}";
        if (RateLimiter::tooManyAttempts($key, self::LOGIN_ATTEMPTS_LIMIT)) {
            $remainingTime = formatSecondsToMinutesTime(RateLimiter::availableIn($key));
            throw new BadRequestHttpException(__('too many attempts: retry after :time minutes', [
                'time' => $remainingTime
            ]));
        }

        // Count failed login attempt
        RateLimiter::hit($key, self::LOGIN_LOCK_SECONDS);

        // Verify password
        if (! Hash::check($request->password, $admin->password)) {
            throw new BadRequestHttpException(__('auth.failed'));
        }

        // Check active
        if (! $admin->is_active) {
            throw new BadRequestHttpException(__('your account is disabled'));
        }

        // DB operations
        try {
            DB::beginTransaction();

            // Clear attempts only after valid credentials
            RateLimiter::clear($key);

            // Create token
            $token = $admin->createToken('api-token');

            if (!$request->remember_me) {
                $token->accessToken->expires_at = now()->addDays(14);
                $token->accessToken->save();
            }

            // Save FCM
            if ($request->fcm_token) {
                $this->fcmTokenRepository->createOrUpdate($admin, $request->device_id, $request->fcm_token, $token->accessToken->id);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        // Response should ALWAYS be outside the try
        return success([
            'token'      => $token->plainTextToken,
            'app_locale' => $admin->app_locale,
            'user'       => ProfileResource::make($admin),
        ]);
    }



    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return success(true);
    }
}
