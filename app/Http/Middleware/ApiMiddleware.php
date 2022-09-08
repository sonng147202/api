<?php

namespace App\Http\Middleware;

use App\Models\System;
use Closure;
use Mockery\Exception;
use App\Lib\OAuth2Helper;
use OAuth2\Request as OAuth2Request;

class ApiMiddleware
{
    public function handle($request, Closure $next)
    {
        $oauth = OAuth2Helper::initOauthServer();
        $globalRequest = OAuth2Request::createFromGlobals();
        if (!$oauth->verifyResourceRequest($globalRequest)) {//verify token
            return \response()->json([
                'result'       =>  System::RESULT_ERROR,
                'current_time' => time(),
                'message'      => 'Token wrong',
                'count'        => 0,
                'data'         => []
            ]);
        } else {
            //do something
            $token = $oauth->getAccessTokenData($globalRequest);
            $request->token = $token;
            //check more app_keycode in header key "AppKey" = sha1($data['app_key_code'])
            //with $data query from oauth_access_tokens
            if ($request->header('AppKey') !== sha1($token['app_keycode'])) {//check sha1(appKeyCode)
                return \response()->json([
                    'result'       =>  System::RESULT_ERROR,
                    'current_time' => time(),
                    'message'      => 'Invalid app key',
                    'count'        => 0,
                    'data'         => []
                ]);
            }
            if ($request->header('UserType') != $token['user_type']) {
                return \response()->json([
                    'result'       =>  System::RESULT_ERROR,
                    'current_time' => time(),
                    'message'      => 'User type wrong',
                    'count'        => 0,
                    'data'         => []
                ]);
            }
        }
        return $next($request);
    }
}
