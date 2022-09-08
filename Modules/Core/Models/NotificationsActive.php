<?php

namespace modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Core\Jobs\SendPushMulti;

class NotificationsActive extends Model
{
    protected $fillable = [];
    protected $table = 'notifications_active';

    protected $guarded = [];


    public static function createNotificationApp($params){
       $data = self::create([
           'title' => $params["title"],
           'object_type' => null,
           'schedule' => null,
           'message' => $params["message"],
           'read_at' => '[]',
           'is_admin_create' => 0,
           'user_id' => $params["user_id"],
           'user_type' => $params["user_type"],
       ]);
       return $data;
    }

    public static function getBaseList($params)
    {
       $query = self::whereNull('deleted_at')->select('*');

       if (isset($params["title"])) {
           $query->where('title','LIKE', '%'.$params['title'].'%');
       }
       if (isset($params["object_type"])) {
           $query->where('object_type',$params["object_type"]);
       }
       $query->where('is_admin_create', 1);
       if (isset($params["schedule"])) {
//            dd($params["schedule"]." 00:00", $params["schedule"]." 23:59");
           $query->whereBetween('schedule', [$params["schedule"]." 00:00", $params["schedule"]." 23:59"]);
       }

       return $query;
    }
    public static function createNotification($params)
    {
       if (!empty($params)) {
           $now = Carbon::now();
           $schedule = Carbon::createFromFormat('Y-m-d H:i', $params["schedule"]);
           $delay = $now->diffInMinutes($schedule);

           $is_success = self::create([
               'title' => $params["title"],
               'read_at' => '[]',
               'object_type' => $params["object_type"],
               'message' => $params["message"],
               'schedule' => $params["schedule"],
               'is_admin_create' => 1
           ]);
//
//            // if (!empty($is_success)) {
//            //     $users = DB::table('oauth_access_tokens')->where('user_type',$params["object_type"])->get();
//            //     // Group by os
//            //     $devicesTokens = [
//            //         'ios' => [],
//            //         'android' => []
//            //     ];
//            //     /* @var $device UserDevice */
//            //     foreach ($users as $user) {
//            //         if (!empty($user->device_token) && $user->device_token != "null") {
//            //             switch (strtolower($user->device_os)) {
//            //                 case 'android':
//            //                     if (!in_array($user->device_token, $devicesTokens['android'])) {
//            //                         $devicesTokens['android'][] = $user->device_token;
//            //                     }
//            //                     break;
//            //                 case 'ios':
//            //                     if (!in_array($user->device_token, $devicesTokens['ios'])) {
//            //                         $devicesTokens['ios'][] = $user->device_token;
//            //                     }
//            //                     break;
//            //             }
//            //         }
//            //     }
//            //     // Send push
//            //     $job =  (new SendPushMulti($devicesTokens, $params["message"], []))->delay(Carbon::now()->addMinutes($delay));
//            //     dispatch($job);
//            // }
           return $is_success;
       }
    }
}
