<?php
namespace Modules\Core\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Modules\Core\Jobs\SendPushMulti;
use Modules\Core\Lib\SendSMS;

class PushMobileChannel
{
    public function send($notifiable, Notification $notification)
    {
        $sendData = $notification->toPushMobile($notifiable);

        if (isset($sendData['message'])) {
            // Get devices
            $devices = $notifiable->routeNotificationForPushMobile();

            if (!empty($devices)) {
                dispatch(new SendPushMulti($devices, $sendData['message'], isset($sendData['extra_data']) ? $sendData['extra_data'] : []));
                return true;
            } else {
                Log::error('[Send Push Mobile] Error: cannot found any device.');
            }
        } else {
            Log::error('[Send Push Mobile] Error: missing message in send data.');
            Log::error('[Send Push Mobile] Send data: ' . json_encode($sendData));
        }
    }
}