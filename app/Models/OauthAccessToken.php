<?php

namespace App\Models;

use App\Lib\Functions;
use App\Mail\Register;
use Illuminate\Database\Eloquent\Model;
use App\Lib\OAuth2Helper;
use Illuminate\Support\Facades\Mail;
use Mockery\Exception;
use OAuth2\Request as OAuth2Request;
use OAuth2\Storage\Memory;
use OAuth2\GrantType\UserCredentials;
use OAuth2\GrantType\ClientCredentials;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Tests\Fixtures\ClearableService;
use DB;
use App\Models\User;

class OauthAccessToken extends Model
{
    protected $table = 'oauth_access_tokens';
    protected $guarded = [];

    const TYPE_CUSTOMER = 0;
    const TYPE_AGENCY = 1;

    /**
     * @param $customerId
     * @return array
     */
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

    public static function generateToken($request, $user, $userId)
    {
        $oauth = OAuth2Helper::initOauthServer();
        //$users = ['user123' => ['password' => 'admin123']];//data get from db to check with var from post
        $storage = new Memory(array('user_credentials' => $user));//save to memory
        $grantType = new UserCredentials($storage);
        $oauth->addGrantType($grantType);

        $postVar = $request->all();//there are two var $postVar['password'] and $postVar['username']
        $postVar['username'] = $userId;//add key 'username' if  use id of user (or email) instead of username
        //use variable from post - $postVar to compare with data in Memory()
        $oauthRequest = new OAuth2Request($_GET, $postVar, array(), $_COOKIE, $_FILES, $_SERVER);

        $responseBody = $oauth->handleTokenRequest($oauthRequest);

        $data = $responseBody->getParameters();

        if (!array_key_exists('access_token', $data)) {
            return $data;
        }
        //create app_keycode in table 'oauth_access_tokens'
        // app_keycode varchar()
        //update to create 'app_keycode' varchar(256) , 'updated_at' datetime
        $appKeyCode = str_random(256);
        $convertTime = date('Y-m-d H:i:s', strtotime('+1 year', strtotime(date('Y-m-d H:i:s'))));
        $dataUpdate = [
            'expires' => $convertTime,
            'app_keycode' => $appKeyCode,
            'user_type' => $request['user_type'],
            'device_os' => $request['device_os'],
            'os_version' => $request['os_version'],
            'device_token' => $request['device_token']
        ];
        self::where('access_token', $data['access_token'])
            ->update($dataUpdate);//save string app_keycode 256 characters in db
        $dataReturn = [
            'oauth' => $data,
            'appKeyCode' => $appKeyCode
        ];
        return $dataReturn;
    }

    /**
     * check user then generate token
     */
    public static function doLogin($request, $option = 'eroscare')
    {
        $arrayAccessTokenUserType = System::arrayAccessTokenUserType();
        $msg = '';
        $data = '';

        $data = User::select('id', 'password', 'status')
            ->where('email', $request['email'])
            ->where('status', 1)
            ->first();
        if (isset($data)) {
            $dataArray = $data->toArray();
            $checkPass =  Hash::check($request['password'], $dataArray['password']);
            if ($checkPass == true) {
                $user = [
                    $dataArray['id'] => ['password' => $request['password']]
                ];
                //GENERATE
                $rs = self::generateToken($request, $user, $dataArray['id']);
                return $rs;
            } else {
                // $msg = __('api.error_wrong_password');
                $msg = __('Lỗi! Mật khẩu của tài khoản không đúng!');
            }
        }
        else {
            // $msg = __('api.error_user_not_exists');
            $msg = __('Lỗi! Tài khoản không tồn tại!');
        }

        $rs = ['error_msg' => $msg];
        return $rs;
    }

    public static function doLoginYolo($request, $option = 'eroscare')
    {
        $arrayAccessTokenUserType = System::arrayAccessTokenUserType();
        $msg = '';
        $password_yolo = 'passwordyolo';
        if (array_key_exists($request['user_type'], $arrayAccessTokenUserType)) {
            $modelUserType = $arrayAccessTokenUserType[$request['user_type']];
            //check if user exists

            $data = $modelUserType::select('id', 'email', 'phone_number', 'password_yolo', 'status', 'raw_password_vpbank')
                ->where('phone_number', $request['phone'])
                ->orWhere('email', $request['email'])
                ->first();
            if (empty($data)) {
                $rs = ['result' => 0];
                return $rs;
            }
            if ($data->status == 0) {
                $data = $modelUserType::where('id', $data['id'])->update(['status' => System::STATUS_ACTIVE, 'password_yolo' => $password_yolo]);
            } else {
                $data = $modelUserType::where('id', $data['id'])->update(['password_yolo' => bcrypt($request['password_yolo'])]);
            }

            $data = $modelUserType::select('id', 'email', 'password_yolo', 'status', 'raw_password_vpbank')
                ->where('phone_number', $request['phone'])
                ->first();
            if ($data == null) {
                $data = $modelUserType::select('id', 'email', 'password_yolo', 'status', 'raw_password_vpbank')
                    ->where('email', $request['email'])
                    ->first();
            }
            if ($data != null) {
                $data->password_yolo = bcrypt($password_yolo);
                // if ($data->status == System::STATUS_ACTIVE) {//check active
                $dataArray = $data->toArray();
                if ($option == 'vpbank') {
                    $password_yolo = $dataArray['raw_password_vpbank'];
                }
                $checkPass = Hash::check($password_yolo, $dataArray['password_yolo']);
                if ($checkPass == true) {
                    $user = [
                        $dataArray['id'] => ['password' => $password_yolo]
                    ];
                    //GENERATE
                    $rs = self::generateToken($request, $user, $dataArray['id']);
                    $rs['result'] = 1;
                    return $rs;
                } else {
                    $msg = __('api.error_wrong_password');
                }
                // } else {
                //     $msg = __('api.error_status_not_active');
                // }
            } else {
                if ($option == 'vpbank') {
                    //Đăng ký thành viên mới cho vpbank
                    $request['name'] = 'VpBank_' . $request['email'];
                    $request['password'] = !empty($password_yolo) ? $password_yolo : rand(100000, 999999);
                    $request['raw_password_vpbank'] = $password_yolo;
                    $request['is_vpbank_customer'] = 1;
                    $dataArray = Customer::customerRegister($request)->toArray();
                    $user = [$dataArray['id'] => ['password' => $password_yolo]];

                    $rs = self::generateToken($request, $user, $dataArray['id']);
                    return $rs;
                } else {
                    $msg = __('api.error_user_not_exists');
                }
            }
        }
        $rs = ['error_msg' => $msg];
        return $rs;
    }

    public static function checkEmailExists($request, $modelUserType)
    {
        $data = $modelUserType::where('email', $request['email'])->first();
        if ($data != null) {
            return true;
        }
        return false;
    }

    public static function doRegister($request)
    {
        $arrayAccessTokenUserType = System::arrayAccessTokenUserType();
        $rs = [];
        if (array_key_exists($request['user_type'], $arrayAccessTokenUserType)) {
            $modelUserType = $arrayAccessTokenUserType[$request['user_type']];
            $checkEmailExists = self::checkEmailExists($request, $modelUserType);
            // if ($checkEmailExists == false) {
            $activeCode = System::randomStr(64);
            $model = $modelUserType::register($request, $activeCode);
            if (isset($model['id'])) {//create ok => SEND EMAIL
                // http://eroscare.paditech.com/erocare/active?user_type=0&active_code=
                $url = 'https://ebaohiem.com/active?user_type=' .
                    $request['user_type'] . '&active_code=' . $activeCode;
                $data = [
                    'url' => $url,
                    // 'email'=>$request['email'],
                    'username' => $request['username'],
                    'password' => $request['password']
                ];
                // if(isset($request['email'])){
                //     dd(1);
                //     Mail::to($request['email'])->queue(new Register($data));
                // }
                // try {
                //     Mail::to($request['email'])->queue(new Register($data));
                //     $rs['email'] = $request['email'];
                // } catch (Exception $ex) {
                // $rs = ['error_msg'=> $model];
                $rs = $model;
                return $rs;
            } else {
                $rs = ['error_msg' => $model['error_msg']];
            }
            // } else {
            //     $rs = ['error_msg'=>'Email đã tồn tại'];
            // }
        }
        return $rs;
    }

    public
    static function activeUser($request)
    {

        $arrayAccessTokenUserType = System::arrayAccessTokenUserType();
        if (array_key_exists($request['user_type'], $arrayAccessTokenUserType)) {
            $modelUserType = $arrayAccessTokenUserType[$request['user_type']];
            $data = $modelUserType::select('id')->where('active_code', $request['active_code'])
                ->first();
            if ($data != null) {
                $array = $data->toArray();
                $modelUserType::where('id', $array['id'])->update(['status' => System::STATUS_ACTIVE]);
            }
            return $data;
        }
        return [];
    }

    /**
     * Generate token just based on email
     * grant_type = 'client_credentials'
     * $params include 'grant_type', 'client_id', 'client_secret'
     */
    public
    static function generateTokenBaseOnEmail($id, $params)
    {

        $oauth = OAuth2Helper::initOauthServer();
        $clients = array($params['client_id'] => array('client_secret' => $params['client_secret']));
        $storage = new Memory(array('client_credentials' => $clients));
        $grantType = new ClientCredentials($storage);
        $oauth->addGrantType($grantType);
        $params['username'] = $id;
        $oauthRequest = new OAuth2Request($_GET, $params, array(), $_COOKIE, $_FILES, $_SERVER);

        $responseBody = $oauth->handleTokenRequest($oauthRequest);

        $data = $responseBody->getParameters();

        if (!array_key_exists('access_token', $data)) {
            return $data;
        }
        $appKeyCode = str_random(256);
        $convertTime = date('Y-m-d H:i:s', strtotime('+1 year', strtotime(date('Y-m-d H:i:s'))));
        $dataUpdate = [
            'expires' => $convertTime,
            'app_keycode' => $appKeyCode,
            'user_type' => System::TYPE_CUSTOMER,
            'user_id' => $id
        ];
        self::where('access_token', $data['access_token'])
            ->update($dataUpdate);//save string app_keycode 256 characters in db
        $dataReturn = [
            'oauth' => $data,
            'appKeyCode' => $appKeyCode
        ];
        return $dataReturn;
    }

    /**
     * Login social
     *
     */
    public
    static function loginSocial($params, $option = 'eroscare')
    {
        $email = $params['email'];
        $name = $params['name'];
        $customer = Customer::where('email', $email)
            ->first();

        if ($customer != null) {
            $id = $customer->id;
            $token = self::generateTokenBaseOnEmail($id, $params);

        } else {//create customer
            $customer = new Customer();
            $customer->name = $name;
            $customer->email = $email;
            $customer->type_id = System::TYPE_CUSTOMER;
            $customer->is_vip = 0;
            $customer->created_by = 0;
            $customer->save();
            $id = $customer->id;
            $token = self::generateTokenBaseOnEmail($id, $params);
        }
        $rs = [
            'user' => $customer,
            'data' => $token
        ];
        return $rs;
    }
}

