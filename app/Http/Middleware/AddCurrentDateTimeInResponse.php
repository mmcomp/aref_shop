<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AddCurrentDateTimeInResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $content = json_decode($response->content(), true);

        //Check if the response is JSON
        if (json_last_error() == JSON_ERROR_NONE) {

            $response->setContent(json_encode(array_merge(
                $content,
                [
                    'currentDateTime' => Carbon::now()->format('Y-m-d H:i:s')
                ]
            )));
        }

        return $response;
    }
}
