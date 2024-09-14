<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof ModelNotFoundException) {
            Log::error($e);
            return response()->json(['error' => 'Data not found.'], Response::HTTP_NOT_FOUND);
        }

        if ($e instanceof AuthorizationException) {
            Log::error($e);
            return response()->json(['error' => 'Unauthorized.'], Response::HTTP_FORBIDDEN);
        }
        
        if ($e instanceof AuthenticationException) {
            Log::error($e);
            return response()->json(['error' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);

        }

        return parent::render($request, $e);
    }
}
