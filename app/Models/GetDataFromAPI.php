<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class GetDataFromAPI extends Model 
{
  public static function getDataFromApi($so_id_dtac, $url, $method)
  {
    // $type = 'json';
    // $header = [
    //     'Accept:application/json',
    //     'Authority:kd01@ebaohiem.com-F48EB4303E59C320B32065189A65484B',
    //     'Content-Type:application/json'
    // ];

    // $ch = curl_init($url);
    // curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($so_id_dtac, JSON_UNESCAPED_UNICODE));
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_POST, true);
    
    // $result = curl_exec($ch);
    // curl_close($ch);
    
    // try {
    //     if (empty($result)) {
    //         Log::info('[VBI-Service] Send request - Headers: ' . json_encode($header));
    //     }
        
    //     Log::info('[VBI-Service] Send request - Response: ' . $result);
        
    //     $response = json_decode($result);
    //     return $response;
    // } catch (\Exception $ex) {
    //     Log::error($ex->getMessage() . '. File ' . $ex->getFile() . '. Line: ' . $ex->getLine());
    //     Log::info('[VBI-Service] Send request: body data: ' . $result);
        
    //     return false;
    // }
    $curl = curl_init();
    switch ($method){
      case "POST":
         curl_setopt($curl, CURLOPT_POST, 1);
         if ($so_id_dtac)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $so_id_dtac);
         break;
      case "PUT":
         curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
         if ($so_id_dtac)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $so_id_dtac);                
         break;
      default:
         if ($so_id_dtac)
            $url = sprintf("%s?%s", $url, http_build_query($so_id_dtac));
     }
     // OPTIONS:
     curl_setopt($curl, CURLOPT_URL, $url);
     curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Accept:application/json',
        'Authority:kd01@ebaohiem.com-F48EB4303E59C320B32065189A65484B',
        'Content-Type:application/json'
     ));
     curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
     // EXECUTE:
     $result = curl_exec($curl);
     if(!$result){die("Connection Failure");}
     curl_close($curl);
     return $result;
  }
}
