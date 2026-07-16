<?php

use App\Exceptions\Domain\InvalidPickupStatusException;
use App\Exceptions\Domain\SafetyCheckRequiredException;
use App\Exceptions\Domain\UnpaidPaymentExistsException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trim string inputs globally so accidental whitespace never breaks
        // search filters / uniqueness checks. Day-1 nicety; zero cost.
        $middleware->redirectTo(
            fn (Request $request) => $request->is('api/*') ? null : route('login')
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Every api/* request must come back as JSON, never an HTML error page.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );

        // Validation errors → 422 with the ApiResponse envelope.
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                    'errors'  => $e->errors(),
                ], 422);
            }
            return null;
        });

        // Model not found / route not found → 404 with the ApiResponse envelope.
        $exceptions->render(function (
            \Illuminate\Database\Eloquent\ModelNotFoundException |
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                    'errors'  => null,
                ], 404);
            }
            return null;
        });

        // Domain exceptions → appropriate status codes
        $exceptions->render(function (UnpaidPaymentExistsException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors'  => null,
                ], 422);
            }
            return null;
        });

        $exceptions->render(function (InvalidPickupStatusException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors'  => null,
                ], $e->getCode() ?: 409);
            }
            return null;
        });

        $exceptions->render(function (SafetyCheckRequiredException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors'  => null,
                ], 422);
            }
            return null;
        });
    })->create();
