<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Exception;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];
   
    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function(Exception $e, $request) {
            return $this->handleException($request, $e);
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        Log::info('AuthenticationException '. $exception);
        return response()->json(['errors' => ['authentication' => ['Unauthenticated.']]], 401);
    } 
    public function handleException($request, Exception $exception)
    {
        if($exception instanceof AccessDeniedHttpException) {
            return response()->json(['errors' => ['forbidden' => ['Forbidden.']]], 403);
        } else if($exception instanceof NotFoundHttpException) {
            return response()->json(['errors' => ['not_found' => ['Not Found.']]], 404);
        } else if($exception instanceof MethodNotAllowedHttpException) {
            return response()->json(['errors' => ['not_allowed' => ['Method Not allowed']]], 405);
        } 
        // else if($exception instanceof QueryException) {
        //     return response()->json(['error' => 'Server Error.'], 500);
        // }
    }
}
