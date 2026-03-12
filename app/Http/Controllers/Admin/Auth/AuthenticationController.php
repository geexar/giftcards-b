<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Exceptions\ConstraintException;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Repositories\AdminRepository;
use Illuminate\Http\Request;
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

    public function __construct(private readonly AdminRepository $adminRepository) {}

    /**
     * Handle admin API login
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws NotFoundHttpException|ConstraintException
     */
    public function login(LoginRequest $request)
    {
        $admin = $this->adminRepository->getByEmail($request->email);

        // Check if admin exists
        if (! $admin) {
            throw new NotFoundHttpException(__('auth.failed'));
        }

        $key = "login-attempts-admin:$admin->id";

        // Rate-limit login attempts
        if (RateLimiter::tooManyAttempts($key, self::LOGIN_ATTEMPTS_LIMIT)) {
            $remainingTime = formatSecondsToMinutesTime(RateLimiter::availableIn($key));
            throw new BadRequestHttpException(__('too many attempts: retry after :time minutes', ['time' => $remainingTime]));
        }

        // Record this login attempt
        RateLimiter::hit($key, self::LOGIN_LOCK_SECONDS);

        // Verify password
        if (! Hash::check($request->password, $admin->password)) {
            throw new BadRequestHttpException(__('auth.failed'));
        }

        // Check if admin account is active
        if (! $admin->is_active) {
            throw new BadRequestHttpException(__('your account is disabled'));
        }

        // Clear login attempts after successful login
        RateLimiter::clear($key);

        // Generate API token
        $token = $admin->createToken('api-token');

        // Set token expiry if not "remember me"
        if (!$request->remember_me) {
            $token->accessToken->expires_at = now()->addHours(2);
            $token->accessToken->save();
        }

        return success([
            'token' => $token->plainTextToken
        ]);
    }

    /**
     * Logout admin by revoking current API token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return success(true);
    }
}
