<?php

namespace App\Http\Controllers;

use App\Models\System;
use Illuminate\Routing\Controller as BaseController;

class ApiController extends BaseController
{
  
    public function successResponse($data, $message = '')
    {
        return $this->apiResponse(1, $data, $message);
    }

    public function errorResponse($data, $message, $code = 0)
    {
        return $this->apiResponse($code, $data, $message);
    }
    /**
     * message has 3 types:
     * 1: msg success
     * 2: error from try catch
     * 3: error custom (from $data : register, login, ....) - error_msg
     * array empty : 1, message no record
     * fail : 0, message custom => key 'exception' and 'error_msg'
     * fail : 0, message same
     * count: count all record if this api is listing record
     */
    protected function apiResponse($data, $count = null, $returnEmptyArray = null)
    {
        if (empty($data) && $returnEmptyArray == null) {
            $resultCode = System::RESULT_SUCCESS;
            $message = 'Dữ liệu không tồn tại';
            $data = new \stdClass();
        } elseif (array_key_exists('exception', $data)) {
            $resultCode = System::RESULT_ERROR;
            $message = $data['error_msg'];
            $data = new \stdClass();
        } elseif (array_key_exists('error_msg', $data)) {
            $resultCode = System::RESULT_ERROR;
            $message = $data['error_msg'];
            $data = new \stdClass();
        } elseif ($returnEmptyArray != null && empty($data)) {//return array not object if result empty
            $resultCode = System::RESULT_SUCCESS;
            $message = 'Dữ liệu không tồn tại';
            $data = [];
        } else {
            $resultCode = System::RESULT_SUCCESS;
            $message = System::MESSAGE_SUCCESS;
        }
        return \response()->json([
            'result'       => $resultCode,
            'current_time' => time(),
            'message'      => $message,
            'count'        => (int)$count,
            'data'         => $data
        ]);
    }

    protected function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
        //execute post
        $result = curl_exec($ch);
    
        //close connection
        curl_close($ch);
    
        return $result;
    }
}
