<?php

namespace Modules\Api\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\ApiController;
use App\Models\User;
use App\Models\NotificationUser;
use App\Models\Notification;

class NotificationController extends ApiController
{
    // Tin tức: Danh sách
    public function getListNews(Request $request)
    {
        try {
            $params = $request->all();
            $user = User::where('group_id', 3)->find($params['user_id']);
            if($user == null)
            {
                return \response()->json([
                    'result'       => 0,
                    'current_time' => time(),
                    'message'      => 'Error! Không có user như vậy trong hệ thống!',
                    'data'         => null
                ]);
            }

            $notificationIdArr = NotificationUser::where('user_id', $user->id)
                                ->get()
                                ->pluck('notification_id')
                                ->toArray();

            $listData = Notification::select(
                'notifications.id',
                'notifications.subject',
                'notifications.content',
                'notifications.attach_file',
                'notifications.levels',
                'notifications.date_join_start',
                'notifications.date_join_end',
                'notifications.offices',
                'notifications.is_office_director',
                'notifications.type',
                'notifications.category',
                'notifications.image',
                'notifications.time_send',
                'notifications.created_at',
                'notifications.description',
                'notifications.display_date'
            )
            ->whereIn('notifications.id', $notificationIdArr)
            ->where('notifications.type', 2)
            ->where('notifications.category', 2)
            ->orderBy('notifications.created_at','DESC')
            ->paginate(12)
            ->toArray();

            $data = $listData;
            
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data, null, 1);
    }

    // Chi tiết tin tức, chi tiết thông báo
    public function getNewsDetail(Request $request)
    {
        try {
            $params = $request->all();
            $user = User::where('group_id', 3)->find($params['user_id']);
            if($user == null)
            {
                return \response()->json([
                    'result'       => 0,
                    'current_time' => time(),
                    'message'      => 'Error! Không có user như vậy trong hệ thống!',
                    'data'         => null
                ]);
            }

            $tmpData = Notification::select(
                'notifications.id',
                'notifications.subject',
                'notifications.content',
                'notifications.attach_file',
                'notifications.levels',
                'notifications.date_join_start',
                'notifications.date_join_end',
                'notifications.offices',
                'notifications.is_office_director',
                'notifications.type',
                'notifications.category',
                'notifications.image',
                'notifications.time_send',
                'notifications.created_at',
                'notifications.description',
                'notifications.display_date'
            )
            ->where('notifications.id', $params['notification_id'])
            ->first();

            $dataOther = Notification::select(
                'notifications.id',
                'notifications.subject',
                'notifications.content',
                'notifications.attach_file',
                'notifications.levels',
                'notifications.date_join_start',
                'notifications.date_join_end',
                'notifications.offices',
                'notifications.is_office_director',
                'notifications.type',
                'notifications.category',
                'notifications.image',
                'notifications.time_send',
                'notifications.created_at',
                'notifications.description',
                'notifications.display_date'
            )
            ->where('notifications.id', '<>', $params['notification_id'])
            ->where('type', 2)
            ->where('category', $tmpData->category)
            ->offset(0)
            ->limit(5)
            ->orderBy('id','DESC')
            ->get();

            NotificationUser::where('user_id', $params['user_id'])
            ->where('notification_id', $params['notification_id'])
            ->update([
                'readed' => 1,
                'date_read' => date('Y-m-d H:i:s')
            ]);

            $data = [
                'data' => $tmpData,
                'dataOther' => $dataOther
            ];

        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data, null, 1);
    }

    // Thông báo: Danh sách
    public function getListNotify(Request $request)
    {
        try {
            $params = $request->all();
            $user = User::where('group_id', 3)->find($params['user_id']);
            if($user == null)
            {
                return \response()->json([
                    'result'       => 0,
                    'current_time' => time(),
                    'message'      => 'Error! Không có user như vậy trong hệ thống!',
                    'data'         => null
                ]);
            }

            $notificationIdArr = NotificationUser::where('user_id', $user->id)
                                ->get()
                                ->pluck('notification_id')
                                ->toArray();

            $listData = Notification::select(
                'notifications.id',
                'notifications.image',
                'notifications.subject',
                'notifications.content',
                'notifications.created_at',
                'notifications.description',
                'notifications.type',
                'notifications.category'
            )
            ->whereIn('notifications.id', $notificationIdArr)
            ->where('notifications.type', 2)
            ->where('notifications.category', 1)
            ->orderBy('notifications.created_at','DESC')
            ->paginate(12)
            ->toArray();

            $data = $listData;
            
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data, null, 1);
    }

    // Danh sách khuyến mãi
    public function getListPromotion(Request $request)
    {
        try {
            $params = $request->all();
            $user = User::where('group_id', 3)->find($params['user_id']);
            if($user == null)
            {
                return \response()->json([
                    'result'       => 0,
                    'current_time' => time(),
                    'message'      => 'Error! Không có user như vậy trong hệ thống!',
                    'data'         => null
                ]);
            }

            $notificationIdArr = NotificationUser::where('user_id', $user->id)
                                ->get()
                                ->pluck('notification_id')
                                ->toArray();

            $data = Notification::select(
                'notifications.id',
                'notifications.subject',
                'notifications.content',
                'notifications.attach_file',
                'notifications.levels',
                'notifications.date_join_start',
                'notifications.date_join_end',
                'notifications.offices',
                'notifications.is_office_director',
                'notifications.type',
                'notifications.category',
                'notifications.image',
                'notifications.time_send',
                'notifications.created_at',
                'notifications.description',
                'notifications.display_date'
            )
            ->whereIn('notifications.id', $notificationIdArr)
            ->where('notifications.type', 2)
            ->where('notifications.category', 4)
            ->orderBy('notifications.created_at','DESC')
            ->paginate(12)
            ->toArray();

        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data, null, 1);
    }
}
