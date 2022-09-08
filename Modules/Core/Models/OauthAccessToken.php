<?php

namespace Modules\Core\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Carbon\Carbon;
use Modules\Core\Lib\PushNotification;
use DB;

class OauthAccessToken extends Model
{
    protected $guarded = [];

    const TYPE_CUSTOMER = 0;
    const TYPE_AGENCY = 1;

    /**
     * @param $customerId
     * @return array
     */

    static function pushNotificationByFcm($userId, $userType, $title, $message) {
        try {
            NotificationsActive::createNotificationApp([
                'title' => $title,
                'message' => $message,
                'user_id' => $userId,
                'user_type' => $userType
            ]);
            self::pushNotificationIos($title, $message, $userId, $userType);
            self::pushNotificationAdroid($title, $message, $userId, $userType);
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

     static function pushNotificationIos($title, $message, $userId, $userType) {
        $user = DB::table('oauth_access_tokens')
                ->where('device_os','ios')
                ->where('user_type',$userType)
                ->where('user_id',$userId)
                ->orderBy('updated_at', 'desc')
                ->first();
        if(!empty($user) && $user->driver_token_valid == 1 ) {
            $deviceToken = $user->device_token;
            if(!empty($deviceToken)) {
                $push = PushNotification::sendNotification($deviceToken, $title , $message);
                $pushData = json_decode($push);
                if($pushData->success != 1) {
                     DB::table('oauth_access_tokens')->where('id', $user->id)->update(['driver_token_valid' => 0]);
                }
            }
        }        
    }

     static function pushNotificationAdroid($title, $message, $userId, $userType) {
        $user = DB::table('oauth_access_tokens')
                ->where('device_os','android')
                ->where('user_type',$userType)
                ->where('user_id',$userId)
                ->orderBy('updated_at', 'desc')
                ->first();
        if(!empty($user) && $user->driver_token_valid == 1 ) {
            $deviceToken = $user->device_token;
            if(!empty($deviceToken)) {
                $push = PushNotification::sendNotification($deviceToken, $title , $message);
                $pushData = json_decode($push);
                if($pushData->success != 1) {
                     DB::table('oauth_access_tokens')->where('id', $user->id)->update(['driver_token_valid' => 0]);
                }
            }
        }        
    }

    public static function getByCustomer($customerId)
    {
        $tokens = self::where('user_type', self::TYPE_CUSTOMER)->where('user_id', $customerId)->get()->toArray();

        if ($tokens) {
            // Group by os
            $tmpData = [];
            foreach ($tokens as $token) {
                if (!empty($token['device_os'])) {
                    if (!isset($tmpData[$token['device_os']])) {
                        $tmpData[$token['device_os']] = [];
                    }

                    $tmpData[$token['device_os']][] = $token['device_token'];
                } else {
                    if (!isset($tmpData['other'])) {
                        $tmpData['other'] = [];
                    }

                    $tmpData['other'][] = $token['device_token'];
                }
            }

            $tokens = $tmpData;
        }

        return $tokens;
    }

    /**
     * @param $customerId
     * @return array
     */
    public static function getByAgency($agencyId)
    {
        $tokens = self::where('user_type', self::TYPE_AGENCY)->where('user_id', $agencyId)->get()->toArray();

        if ($tokens) {
            // Group by os
            $tmpData = [];
            foreach ($tokens as $token) {
                if (!empty($token['device_os'])) {
                    if (!isset($tmpData[$token['device_os']])) {
                        $tmpData[$token['device_os']] = [];
                    }

                    $tmpData[$token['device_os']][] = $token['device_token'];
                } else {
                    if (!isset($tmpData['other'])) {
                        $tmpData['other'] = [];
                    }

                    $tmpData['other'][] = $token['device_token'];
                }
            }

            $tokens = $tmpData;
        }

        return $tokens;
    }
}
