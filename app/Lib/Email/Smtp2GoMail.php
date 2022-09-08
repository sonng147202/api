<?php
/**
 * Created by PhpStorm.
 * User: nguyentiendong
 * Date: 7/24/18
 * Time: 17:25
 */

namespace App\Library\Email;

class Smtp2GoMail {

    protected $url;
    protected $version;
    protected $url_send_mail_api;
    protected $api_key;

    public function __construct()
    {
        // $this->api_key = 'api-AAE6B620A6D211EABC11F23C91C88F4E';
        $this->api_key = 'api-0D7D7812F5A311EBAB7DF23C91C88F4E';
        $this->url = 'https://api.smtp2go.com';
        $this->version = "v3";
        $this->url_send_mail_api = $this->url.'/'.$this->version.'/email/send';
    }

    public function sendMailApi($to = [], $sender = 'contact@eroscare.com', $subject = '', $html_body = '',$cc = []) {
        $fields = array(
            'api_key' => $this->api_key,
            'to' => $to,
            'sender' => $sender,
            'cc' => $cc,
            'subject' => $subject,
            'html_body' => $html_body
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url_send_mail_api,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return [
                'req' => json_encode($fields),
                'data' => $err,
            ];
        } else {
            return [
                'req' => json_encode($fields),
                'data' => $response,
            ];
        }
    }
}