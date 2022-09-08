<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class GetAPI extends Model 
{
    //send sms when create agency
    public static function sendSmsAgency($arrayParams)
    {
        $APIKey= "B3F762BC105820379C794B1158EE57";
        $SecretKey= "A5FD32EE9F74EACCB1FE6CE6073C26";
        $BRANDNAME= "Eroscare";
        $YourPhone= $arrayParams['phone'];
        $ch = curl_init();

        
        $SampleXml = "<RQST>"
                               . "<APIKEY>". $APIKey ."</APIKEY>"
                               . "<SECRETKEY>". $SecretKey ."</SECRETKEY>"                                    
                               . "<ISFLASH>0</ISFLASH>"
                               . "<SMSTYPE>2</SMSTYPE>"
                               . "<CONTENT>". $arrayParams['content'] ."</CONTENT>"
                               . "<BRANDNAME>".$BRANDNAME."</BRANDNAME>"
                               . "<CONTACTS>"
                               . "<CUSTOMER>"
                               . "<PHONE>". $YourPhone ."</PHONE>"
                               . "</CUSTOMER>"                               
                               . "</CONTACTS>"
                               . "</RQST>";
                                    
                               
        curl_setopt($ch, CURLOPT_URL,            "http://api.esms.vn/MainService.svc/xml/SendMultipleMessage_V4/" );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_POST,           1 );
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $SampleXml ); 
        curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain')); 

        $result=curl_exec ($ch);        
        $xml = simplexml_load_string($result);

        if ($xml === false) {
            die('Error parsing XML');   
        }
        //now we can loop through the xml structure
        //Tham khao them ve SMSTYPE de gui tin nhan hien thi ten cong ty hay gui bang dau so 8755... tai day :http://esms.vn/SMSApi/ApiSendSMSNormal
    }
}
