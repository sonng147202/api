<?php

namespace modules\Core\Http\Controllers;

use App\Models\KMsg;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;
use Modules\Core\Emails\SendMailToCustomer;
use Modules\Core\Models\NotificationsActive;
use Modules\Insurance\Lib\FCMPushMessage;
use Redirect;
use Modules\Insurance\Models\MailQueue;

class NotificationController extends Controller
{

    private $api;

    
    function __construct()
    {
        $this->api = new FCMPushMessage();
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        $params = $request->all();
        $query = NotificationsActive::getBaseList($params);
        $notifications = $query->orderByDesc('id')->paginate();


        return view('core::notification.index',compact('notifications', 'params'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('core::notification.create');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function store(Request $request)
    {
        $result = new KMsg();

        $params = $request->all();

        if (!empty($params)) {
            //Store data
            $validatorArray = [
                'title' => 'required',
                'message' => 'required',
                'object_type' => 'required',
                'schedule' => 'required'

            ];


            $validator = Validator::make($request->all(), $validatorArray);
            if ($validator->fails()) {
                $result->message = $result->getMessageErros($validator->errors());

                return [
                    'status' => 0,
                    'message' => $result->message,
                ];
            }
            $notification = NotificationsActive::createNotification($params);
            // $this->api->send($params['title'], $params['message']);
            
            if (!empty($notification)) {
                
                return [
                    'status' => 1
                ];
            }
            return [
                'status' => 0,
                'message' => 'Error save notification'
            ];

            //Job
        }
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show()
    {
        return view('core::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Request $request)
    {
        $params = $request->all();
        if (!empty($params["noti_id"])) {
            $noti = NotificationsActive::findOrFail($params["noti_id"]);

            return view('core::notification.edit',compact('noti'));
        }
        return view('core::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function notificationDestroy($id)
    {
        $notification = NotificationsActive::where("id", $id)->first();
        if ($notification) {
            //delete record firebase
            // $notification = NotificationsActive::deleteNotification($notification);
            //end
            $notification->delete();
            return Redirect::route('core.notification.index')->with('msg_success','Xóa thông báo thành công');
        } else {
            return Redirect::route('core.notification.index')->withErrors(["Bản ghi không tồn tại!"]);
        }
    }

    public function sendEmail(Request $request)
    {
        if ($request->isMethod('POST') && !empty($request->all())) {
            $params = $request->all();
            $this->validate($request,[
                'mailTo' => 'required|email',
                'title' => 'required',
                'content' => 'required'
            ]);

            $response = new KMsg();

            try {

//                $query = Mail::to($params["mailTo"]);
//
//                if (!empty($params["ccEmail"])) {
//                    $query->cc($params["ccEmail"]);
//                }

                $user = Auth::user();
                $params['email_from'] = !empty($user->email) ? $user->email : 'support@eroscare.com';
//                dd($params);
//                $query->queue(new SendMailToCustomer($params));


                MailQueue::saveMailToQueue([
                    'send_to' => json_encode([$params["mailTo"]]),
                    'sender' =>  '<'.$params['email_from'].'>',
                    'cc' => ($params["ccEmail"]) ? json_encode([$params["ccEmail"]]) : '',
                    'subject' => (new SendMailToCustomer($params))->subjectEmail(),
                    'variable' => json_encode([
                        'email' => ['content' => $params->content],
                    ]),
                    'templete' => 'core::notification.emailTemplate'
                ]);

                $response->result = 1;
                $response->message = "Gửi mail thành công";

                return \response()->json($response);

            } catch (Exception $ex) {
                $response->result = 0;
                $response->message = "Gửi mail thất bại";

                return \response()->json($response) ;
            }
        } else {
//            dd(Auth::user());
            return view('core::notification.sendEmail');
        }
    }
}
