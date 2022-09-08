<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ApiTracking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response) {
        if (config('eroscare.api_tracking') == 1) {
            try {
                $client = new Client();
                $client->post(config('eroscare.api_tracking_url'),
                    [
                        'form_params' => [
                            'app_id'           => config('eroscare.app_id'),
                            'device_id'        => isset($request->input['device_id']) ? $request->input['device_id'] : '',
                            'device_os'        => isset($request->input['device_os']) ? $request->input['device_os'] : '',
                            'os_version'       => isset($request->input['os_version']) ? $request->input['os_version'] : '',
                            'request_url'      => $request->fullUrl(),
                            'encrypt_params'   => '',
                            'request_params'   => json_encode($request->input()),
                            'response'         => $response->content(),
                            'encrypt_response' => ''
                        ]
                    ]);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage() . '. ' . $ex->getFile() . '. ' . $ex->getLine());
            }
        }
    }
}
