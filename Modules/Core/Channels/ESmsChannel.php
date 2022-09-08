<?php
namespace Modules\Core\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Modules\Core\Lib\SendSMS;

class ESmsChannel
{
    public function send($notifiable, Notification $notification)
    {
        $sendData = $notification->toESms($notifiable);

        if (isset($sendData['message'])) {
            // Send sms
            $smsService = new SendSMS('esms');
            return $smsService->sendSms($notifiable->routeNotificationForESms(), $sendData['message']);
        } else {
            Log::error('[Send SMS ESms] Error: missing message in send data.');
        }
    }
}