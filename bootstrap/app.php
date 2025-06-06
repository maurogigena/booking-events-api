<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function () {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'message' => $e->getMessage() ?: 'Not found.'
            ], 404);
        });
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return response()->json([
                'status' => 404,
                'message' => $e->getMessage() ?: 'Not found.'
            ], 404);
        });
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json([
                'status' => 403,
                'message' => $e->getMessage() ?: 'Forbidden.'
            ], 403);
        });
        $exceptions->render(function (UnauthorizedHttpException $e) {
            return response()->json([
                'status' => 401,
                'message' => 'You are not authorized to access this resource. Please login to continue.',
            ], 401);
        });
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'status' => 401,
                'message' => 'You are not authenticated. Please register or login to continue.',
            ], 401);
        });
        $exceptions->render(function (Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        });
        $exceptions->render(function (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Server error.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        });
    })->create();
