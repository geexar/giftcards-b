<?php

use App\Exceptions\ConstraintException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        using: function () {
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('api', 'setLocale')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('api', 'setLocale', 'pagination.settings')
                ->prefix('api/user')
                ->name('user.')
                ->group(base_path('routes/user.php'));

            Route::middleware('api', 'setLocale', 'pagination.settings')
                ->prefix('api/admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'setLocale' => \App\Http\Middleware\SetLocale::class,
            'pagination.settings' => \App\Http\Middleware\PaginationSettings::class,
            'activeToken' => \App\Http\Middleware\ActiveToken::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions
            ->renderable(function (NotFoundHttpException $e, $request) {
                return error($e->getMessage() ? $e->getMessage() : __('resource not found'), 404);
            })
            ->renderable(function (UnauthorizedHttpException $e, $request) {
                return error($e->getMessage() ? $e->getMessage() : __('unauthorized'), 401);
            })
            ->renderable(function (BadRequestHttpException $e, $request) {
                return error($e->getMessage() ? $e->getMessage() : __('can not process the request'), 401);
            })
            ->renderable(function (ConstraintException $e, $request) {
                return error($e->getMessage() ? $e->getMessage() : __('this action can not be done'), 401);
            });
    })->create();
