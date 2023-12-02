<?php

namespace App\Exceptions;

use App\Http\Resources\ReadingStationOffdaysResource;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        $this->renderable(function (Exception $e, $request) {
            return $this->handleException($request, $e);
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json(['errors' => ['authentication' => ['Unauthenticated.']]], 401);
    }
    public function handleException($request, Exception $exception)
    {
        if ($exception instanceof AccessDeniedHttpException) {
            return response()->json(['errors' => ['forbidden' => ['Forbidden.']]], 403);
        } else if ($exception instanceof NotFoundHttpException) {
            $path = $request->path();
            if (str_starts_with($path, "api/reading-stations/") || str_starts_with($path, "api/reading-station-calls/")) {
                if (str_contains($exception->getMessage(), '[App\Models\ReadingStationSlut]')) {
                    return (new ReadingStationOffdaysResource(null))->additional([
                        'errors' => ['reading_station_slut' => ['Reading station slut not found!']],
                    ])->response()->setStatusCode(404);
                }
                return (new ReadingStationOffdaysResource(null))->additional([
                    'errors' => ['reading_station' => ['Reading station not found!']],
                ])->response()->setStatusCode(404);
            }
            if (str_starts_with($path, "api/users/")) {
                return (new ReadingStationOffdaysResource(null))->additional([
                    'errors' => ['user' => ['User not found!']],
                ])->response()->setStatusCode(404);
            }
            if (str_starts_with($path, "api/reading-station-packages/")) {
                return (new ReadingStationOffdaysResource(null))->additional([
                    'errors' => ['reading_station_package' => ['Reading Station Package not found!']],
                ])->response()->setStatusCode(404);
            }
            if (str_starts_with($path, "api/reading-station-strikes/")) {
                return (new ReadingStationOffdaysResource(null))->additional([
                    'errors' => ['reading_station_strike' => ['Reading Station Strike not found!']],
                ])->response()->setStatusCode(404);
            }
            if (str_starts_with($path, "api/reading-station-absent-reasons/")) {
                return (new ReadingStationOffdaysResource(null))->additional([
                    'errors' => ['reading_station_absent_reason' => ['Reading Station Absent Reason not found!']],
                ])->response()->setStatusCode(404);
            }
            return response()->json(['errors' => ['not_found' => ['Not Found.']]], 404);
        } else if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json(['errors' => ['not_allowed' => ['Method Not allowed']]], 405);
        } else if ($exception instanceof HttpException) {
            return response()->json(['errors' => ['http_exception' => [$exception->getMessage()]]], $exception->getStatusCode());
        }
    }
}
