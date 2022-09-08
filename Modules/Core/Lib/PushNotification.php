<?php

namespace Modules\Core\Lib;

use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;

class PushNotification
{
    public static function sendNotification($deviceToken, $title , $message)
    {
        try {
            return self::send($deviceToken, $title, $message);
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            Log::error('Error send push:');
            Log::error($ex->getMessage());
        }
    }

    public static function send($deviceTokens, $title, $msgBody)
    {
        $key = 'AAAAmY5Mdhs:APA91bF4ISXoT7BIprgKwB_euvt9sjGYLyT3tmt7ZRctApRdQWN8RImLq-aRK93Nve7ATTssjmaoywDGHA4PpSG0YrGbEjSq9yFt60qP7gJUV8wNrj7OUao6IWboUpO3E1MEpw6XFNHn';
        $headers = array(
            'Authorization: key= ' . $key,
            'Content-Type: application/json'
        );
        $fields = array(
            'notification' => [
                'title' => $title,
                'body' => $msgBody
            ],
            'to' => $deviceTokens
        );
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        //curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode($fields) );
        $result = curl_exec($ch );
        if ($result === FALSE) {
            Log::error('FCM error: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);
        return $result;
    }
}
