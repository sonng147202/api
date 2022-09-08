<?php

namespace Modules\Api\Http\Controllers\V1;

use App\Models\OauthAccessToken;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use App\Models\System;
use App\Mail\MailForgotPassword;
use App\Models\Agency;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;

class AccessTokenController extends ApiController
{

    /**
     * $request must have params: grant_type, client_id, client_secret, user_type, email, password
     */
    public function register(Request $request)
    {
        try {
            $data = OauthAccessToken::doRegister($request);
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        if($data['error_msg']){
            return ([
                'result' => 0,
                'messages' => 'false',
                'data' => $data
            ]);
        }
        return $this->apiResponse([
            'result' => 'success',
            'messages' => 'Đăng ký tài khoản thành công!',
            'data' => $data
        ]);
    }

    /**
     * $request must have params: grant_type, client_id, client_secret, user_type, email, password
     */
    public function login(Request $request)
    {
        $data = OauthAccessToken::doLogin($request);
        if (array_key_exists('oauth', $data)) {
            $user = System::getUserWhenLogin($data['oauth']['access_token'], $request['user_type']);
            if (!empty($user['avatar'])) {
                if (is_string($user['avatar'])) {
                    $avatar = json_decode($user['avatar'], true);
                    if (!empty($avatar)) {
                        $user['avatar'] = $avatar;
                        if (!empty($avatar[0])) {
                            $user['avatar'] = $avatar[0];
                        }
                    }
                } else {
                    $user['avatar'] = new \stdClass();
                }
            } else {
                $user['avatar'] = new \stdClass();
            }
            $rs = [
                'access_token' => $data['oauth']['access_token'],
                'UserType' => (int)$request['user_type'],
                'appKey' => $data['appKeyCode'],
                'data' => $user
            ];
        } else {
            $rs = $data;
        }
        return $this->apiResponse($rs);
    }
    public function forgotPassword (Request $request){
        $result = Agency::where('email', $request->email)->first();
        if($result != null) {
            $passwordOrigin = str_random(8);
            $result->update([
                'password' => Hash::make($passwordOrigin),
            ]);
            // send mail thay đổi mật khẩu
            if(!empty($result->email)) {
                $parramsMail = [
                    'send_to' => $result->email,
                    'variable' => [
                        'data' => [
                            'name' => $result->name,
                            'phone_number' => $result->phone,
                            'email' => $result->email,
                            'password' => $passwordOrigin,
                        ],
                    ],
                    'email_type' => 6,
                ];
                System::sendMailNow($parramsMail);
            }
//            Mail::to($request->email)->send(new MailForgotPassword($result, $passwordOrigin));
            return \response()->json(['result'=>1, 'messages'=>'success']);
        }
        else{
            return \response()->json(['result'=>0, 'messages'=>'fail']);
        }
    }


    public function yolologin(Request $request)
    {
        $data = OauthAccessToken::doLoginYolo($request);
        if($data['result']==1) {
            if (array_key_exists('oauth', $data)) {
                $user = System::getUserWhenLogin($data['oauth']['access_token'], $request['user_type']);
                if (!empty($user['avatar'])) {
                    if (is_string($user['avatar'])) {
                        $avatar = json_decode($user['avatar'], true);
                        if (!empty($avatar)) {
                            $user['avatar'] = $avatar;
                            if (!empty($avatar[0])) {
                                $user['avatar'] = $avatar[0];
                            }
                        }
                    } else {
                        $user['avatar'] = new \stdClass();
                    }
                } else {
                    $user['avatar'] = new \stdClass();
                }
                $rs = [
                    'access_token' => $data['oauth']['access_token'],
                    'UserType' => (int)$request['user_type'],
                    'appKey' => $data['appKeyCode'],
                    'data' => $user
                ];
            } else {
                $rs = $data;
            }
            return $this->apiResponse($rs);
        }else{
            return \response()->json([
                'result'       => 0,
                'current_time' => time(),
                'message'      => 'error',
                'data'         => null
            ]);
        }
    }

    public function loginVpBank(Request $request)
    {
        $data = OauthAccessToken::doLogin($request, 'vpbank');
        if (array_key_exists('oauth', $data)) {
            $user = System::getUserWhenLogin($data['oauth']['access_token'], $request['user_type']);
            if (!empty($user['avatar'])) {
                if (is_string($user['avatar'])) {
                    $avatar = json_decode($user['avatar'], true);
                    if (!empty($avatar)) {
                        $user['avatar'] = $avatar;
                        if (!empty($avatar[0])) {
                            $user['avatar'] = $avatar[0];
                        }
                    }
                } else {
                    $user['avatar'] = new \stdClass();
                }
            } else {
                $user['avatar'] = new \stdClass();
            }
            $rs = [
                'access_token' => $data['oauth']['access_token'],
                'UserType' => (int)$request['user_type'],
                'appKey' => $data['appKeyCode'],
                'data' => $user
            ];
        } else {
            $rs = $data;
        }
        return $this->apiResponse($rs);
    }



    /**
     * Login social
     */
    public function loginSocial(Request $request)
    {
        $params = $request->all();
        $rs = OauthAccessToken::loginSocial($params);
        if (array_key_exists('oauth', $rs['data'])) {
            $rs = [
                'access_token' => $rs['data']['oauth']['access_token'],
                'UserType' => (int)$request['user_type'],
                'appKey' => $rs['data']['appKeyCode'],
                'data' => $rs['user']
            ];
        }
        return $this->apiResponse($rs);
    }

    public function active(Request $request)
    {
        $data = OauthAccessToken::activeUser($request);
        return $this->apiResponse($data);
    }
    public function add_customer(Request $request)
    {
        $input = $request->all();
        $customer = new Customer();
        $customer->type_id = 1;
        $customer->name = $input['name'];
        $customer->phone_number = $input['phone_number'];
        $customer->email = $input['email'];
        $customer->password = bcrypt(env('PASSS_DEFAULT'));
        $customer->password_yolo = bcrypt('passwordyolo');
        $customer->is_vip = System::IS_NOT_VIP;
        $customer->address = isset($input['address']) ? $input['address'] : '';
        $customer->identity_card = $input['identity_card'];
        $customer->date_of_birth = System::convertDateToStandard($input['date_of_birth']);
        $customer->customer_manager_id = 1;
        $customer->classify =   -1;
        $customer->status = 1;
        //$customer->invitation_code = isset($input['invitation_code']) ? $input['invitation_code'] : '';
        $customer->code_customer = $this->getCodeCustomer();
        $customer->sex = isset($input['sex']) ? $input['sex'] : -1;
        $customer->source = !empty($input['source']) ? $input['source'] : 0;
        $customer->save();
        if  ($customer) {
            return json_encode($customer);
        } else {
            return '';
        }
    }
    public function getCodeCustomer(){
        do{
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $rand = '';
            for ($i = 0; $i < 6; $i++) {
                $rand .= $characters[rand(0, $charactersLength - 1)];
            }
        }while(!empty(Customer::where('code_customer',$rand)->first()));
        return $rand;
    }
}
