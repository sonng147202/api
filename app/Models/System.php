<?php

namespace App\Models;

use App\Mail\Contact;
//use App\Models\System;
use App\Mail\ForgotPassword;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\MailQueue;

class System extends Model
{
    //const TOKEN_BEARER_HEADER_NAME = 'ErosCareApp';//move to .env

    const API_VERSION = 'v1';
    const TYPE_CUSTOMER = 0;
    const TYPE_AGENCY = 1;
    const TYPE_USER = 2;
    const TYPE_AGENCY_IN_API_ADMIN = 2;

    const IS_AGENCY = 1;

    const IS_NOT_VIP = 0;
    const IS_VIP = 1;

    const RESULT_ERROR = 0;
    const RESULT_SUCCESS = 1;

    const URL_API_GET_FORM_DATA = '/api/v1/insurance/filter-form';
    const URL_API_GET_PRODUCT = '/api/v1/products';
    const URL_API_GET_INFO_PAY = '/api/v1/product/price';
    const URL_API_CREATE_CONTRACT = '/api/v1/contract/create';
    const API_ADMIN_CONTRACT_GET_CREATE = '/api/v1/contract/get-create-form';
    const API_ADMIN_CONTRACT_CREATE = '/api/v1/contract/store';
    const API_ADMIN_CONTRACT_UPDATE_FILE_ID = '/api/v1/contract/update-file-id';
    const API_ADMIN_CONTRACT_GET_CERTIFICATE = '/api/v1/contract/active-certificate';
    const API_UPLOAD_FILE = '/api/v1/insurance/upload-file';
    const URL_API_UPDATE_CONTRACT_PAYMENT = '/api/v1/contract/update-payment';
    const URL_API_UPLOAD_CONTRACT_FILE = '/api/v1/insurance/upload-contract-file';
    const URL_API_GET_QUOTATION = '/api/v1/insurance/get-quotation';
    const URL_API_UPLOAD_IMAGE = '/api/v1/upload-image';
    const URL_API_GET_CONTRACT_CREATE_FORM = '/api/v1/contract/get-create-form';
    const URL_API_GET_CONTRACT_PRICES = '/api/v1/contract/get-prices';
    const URL_API_GET_PRODUCT_PRICES = '/api/v1/product/get-prices';
    const URL_API_GET_EXTRA_PRODUCT_FOR_PRODUCT = '/api/v1/get-extra-product-for-product';
    const URL_API_SAVE_MEMBER_ADVISOR = '/api/v1/member-adivisor/save-member-advisor';
    const URL_API_SEND_MAIL_QUEUE = '/api/v1/send-mail-queue';
    const URL_API_SEND_MAIL_NOW = '/api/v1/send-mail-now';

    const STATUS_DELETED = -1;
    const STATUS_UNACTIVE = 0;
    const STATUS_ACTIVE = 1;

    const MESSAGE_SUCCESS = 'Successful';

    const FILE_TYPE_OWNER = 'owner';
    const FILE_TYPE_BENEFICE = 'benificary';
    const FILE_TYPE_CERTIFICATE = 'certificate';
    const FILE_TYPE_OTHER = 'other';

    const PRICE_TYPE_PERSONAL = 'personal_fee';
    const PRICE_TYPE_FAMILY = 'family_fee';
    const PRICE_TYPE_ENTERPRISE = 'enterprise_fee';

    const BENEFICIARY_TYPE_PERSON = 1;

    static public $fileTypeTitle = [
        self::FILE_TYPE_OWNER => 'Thông tin chủ hợp đồng',
        self::FILE_TYPE_BENEFICE => 'Thông tin đối tượng hưởng bảo hiểm',
        self::FILE_TYPE_CERTIFICATE => 'Giấy chứng nhận bảo hiểm',
        self::FILE_TYPE_OTHER => 'Thông tin khác'
    ];

    public static function arrayAccessTokenUserType()
    {
        $rs = [
            self::TYPE_CUSTOMER => new Customer(),
            self::TYPE_AGENCY => new Agency()
        ];
        return $rs;
    }

    public static function sendMailQueue($arrayParams)
    {
        $curl = curl_init();
        $url = config('app.url_api_admin') . '/api/v1/send-mail-queue';
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($arrayParams),
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
            ),
        ));
        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;
    }

    public static function pushNotificationByFcm($arrayParams)
    {
        $curl = curl_init();
        $url = config('app.url_api_admin') . '/api/v1/push-notification';
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($arrayParams),
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
            ),
        ));
        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;
    }

    public static function sendMailNow($arrayParams)
    {
        $curl = curl_init();
        $url = config('app.url_api_admin') . '/api/v1/send-mail-now';
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($arrayParams),
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
            ),
        ));
        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;
    }

    public static function getDataFromApi($arrayParams, $url)
    {
        //add base url of api
        $url = config('app.url_api_admin') . $url;
        // Get cURL resource
        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'cURL Request',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query($arrayParams)
        ));

        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);
        return $resp;
    }

    //send sms when create agency
    public static function sendSmsAgency($arrayParams)
    {
        $APIKey = "B3F762BC105820379C794B1158EE57";
        $SecretKey = "A5FD32EE9F74EACCB1FE6CE6073C26";
        $BRANDNAME = "Eroscare";
        $YourPhone = $arrayParams['phone'];
        $ch = curl_init();


        $SampleXml = "<RQST>"
            . "<APIKEY>" . $APIKey . "</APIKEY>"
            . "<SECRETKEY>" . $SecretKey . "</SECRETKEY>"
            . "<ISFLASH>0</ISFLASH>"
            . "<SMSTYPE>2</SMSTYPE>"
            . "<CONTENT>" . $arrayParams['content'] . "</CONTENT>"
            . "<BRANDNAME>" . $BRANDNAME . "</BRANDNAME>"
            . "<CONTACTS>"
            . "<CUSTOMER>"
            . "<PHONE>" . $YourPhone . "</PHONE>"
            . "</CUSTOMER>"
            . "</CONTACTS>"
            . "</RQST>";


        curl_setopt($ch, CURLOPT_URL, "http://api.esms.vn/MainService.svc/xml/SendMultipleMessage_V4/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $SampleXml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));

        $result = curl_exec($ch);
        $xml = simplexml_load_string($result);

        if ($xml === false) {
            die('Error parsing XML');
        }
        //now we can loop through the xml structure
        //Tham khao them ve SMSTYPE de gui tin nhan hien thi ten cong ty hay gui bang dau so 8755... tai day :http://esms.vn/SMSApi/ApiSendSMSNormal
    }

    public static function getDataFromApiWithFile($arrayParams, $url)
    {
        //add base url of api
        $url = config('app.url_api_admin') . $url;
        // Get cURL resource
        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'cURL Request',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $arrayParams
        ));

        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:multipart/form-data']);

        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);
        return $resp;
    }

    /**
     * get id of user in table 'oauth_access_tokens' save in field 'user_id'
     */
//    public static function getUserByToken($authorization, $userType)
//    {
//        $token = str_replace(self::TOKEN_BEARER_HEADER_NAME.' ', '', $authorization);
//        $user = OauthAccessToken::select('user_id')
//            ->where('access_token', $token)
//            ->where('user_type', $userType)
//            ->first();
//        return $user;
//    }

    /**
     * get user id based on user_type => get field email from table 'customer' or 'insurance_agency'
     */
//    public static function getUserIdByEmailAndType($userType, $email)
//    {
//        $arrayUserModel = self::arrayAccessTokenUserType();
//        if (array_key_exists($userType, $arrayUserModel)) {
//            $userByType = $arrayUserModel[$userType]::select('id')->where('email', $email)->first();
//            if ($userByType != null) {
//                $userByTypeArray = $userByType->toArray();
//                $userIdByType = $userByTypeArray['id'];
//                return $userIdByType;
//            }
//        }
//        return '';
//    }

    public static function getUserByTypeAndAccessToken($request)
    {
        $token = $request->token;
        return $token['user_id'];
    }

    public static function getUserWhenLogin($token, $userType)
    {
        $user = OauthAccessToken::select('user_id')
            ->where('access_token', $token)
            ->where('user_type', $userType)
            ->first();
        if ($user != null) {
            $array = $user->toArray();
            $id = $array['user_id'];
            $arrayUserModel = self::arrayAccessTokenUserType();
            if (array_key_exists($userType, $arrayUserModel)) {
                $data = $arrayUserModel[$userType]::where('id', $id)->first();
                return $data;
            }
        }
        return [];
    }

    public static function randomStr($length)
    {
        $keyspace = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    public static function randomStrNumber($length)
    {
        $keyspace = '123456789';
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    public static function convertDateToTimestamp($array, $arrayFieldConvert)
    {
        $rs = [];
        foreach ($array as $row) {
            foreach ($arrayFieldConvert as $r) {
                if (array_key_exists($r, $row)) {
                    if ($row[$r] != null) {
                        $row[$r] = strtotime($row[$r]);
                    } else {
                        $row[$r] = 0;
                    }
                }
            }
            $rs[] = $row;
        }
        return $rs;
    }

    public static function convertDate($dateConvert, $hour)
    {
        $date = Carbon::createFromFormat('d/m/Y', $dateConvert);
        if ($hour == false) {
            // $dateFormatted = $date->format('d/m/Y');
            $dateFormatted = $date;
        } else {
            // $dateFormatted = $date->format('d/m/Y H:i');
            $dateFormatted = $date->format('d/m/Y H:i');
        }

        return $dateFormatted;
    }

    public static function convertDateToStandard($dateConvert)
    {
        $date = Carbon::createFromFormat('d/m/Y', $dateConvert);
        $newDate = $date->format('Y-m-d');
        return $newDate;
    }

    /**
     * convert date in filter Data to d/m/Y, use for api-admin : find-product, price, create contract
     */
    public static function convertFilterDataDate($filterData)
    {
        if (array_key_exists('trip_start', $filterData) && array_key_exists('trip_end', $filterData)) {
            $filterData['trip_start'] = self::convertDate($filterData['trip_start'], false);
            $filterData['trip_end'] = self::convertDate($filterData['trip_end'], false);
        }
        if (array_key_exists('start_time', $filterData) && array_key_exists('end_time', $filterData) && $filterData['insurance_type_id'] != 23) {
            $filterData['start_time'] = self::convertDate($filterData['start_time'], true);
            if (!empty($filterData['end_time'])) {
                $filterData['end_time'] = self::convertDate($filterData['end_time'], true);
            }
        }
        return $filterData;
    }

//    /**
//     * convert date in filter Data to d/m/Y, use for api-admin : find-product, price, create contract
//     */
//    public static function convertFilterDataDate($filterData)
//    {
//        if (array_key_exists('trip_start', $filterData) && array_key_exists('trip_end', $filterData)) {
//            $filterData['trip_start'] = date('d/m/Y', strtotime($filterData['trip_start']));
//            $filterData['trip_end'] = date('d/m/Y', strtotime($filterData['trip_end']));
//        }
//        if (array_key_exists('start_time', $filterData) && array_key_exists('end_time', $filterData)) {
//            $filterData['start_time'] = date('d/m/Y H:i', strtotime($filterData['start_time']));
//            $filterData['end_time'] = date('d/m/Y H:i', strtotime($filterData['end_time']));
//        }
//        return $filterData;
//    }

    public static function getProfile($request)
    {
        $rs = [];
        $userId = self::getUserByTypeAndAccessToken($request);
        $arrayAccessTokenUserType = self::arrayAccessTokenUserType();
        $userType = $request->header('UserType');
        if (array_key_exists($userType, $arrayAccessTokenUserType)) {
            $model = $arrayAccessTokenUserType[$userType];
            $data = $model::getProfile($userId);
            if ($data != null) {
                $rs = $data->toArray();
                if (!empty($rs['avatar'])) {
                    // $avatar = json_decode($rs['avatar'], true);
                    // $rs['avatar'] = $avatar;
                    if (!empty($avatar[0])) {
                        $rs['avatar'] = $avatar[0];
                    }
                }
            }
        }
        return $rs;
    }

    public static function sendEmailForgotPassword($request)
    {
        $rs = [];
        $isEmailExist = [];
        $isPhoneExist = [];
        $arrayAccessTokenUserType = self::arrayAccessTokenUserType();
        $userType = $request->header('UserType');
        if (array_key_exists($userType, $arrayAccessTokenUserType)) {
            $model = $arrayAccessTokenUserType[$userType];
            $activeCode = self::randomStrNumber(6);
            if (isset($request['email'])) {
                $isEmailExist = $model->where('email', $request['email'])
                    ->first();
            }
            if (isset($request['phone'])) {
                if ($userType == 1) {
                    $isPhoneExist = $model->where('phone', $request['phone'])
                        ->first();
                } else {
                    $isPhoneExist = $model->where('phone_number', $request['phone'])
                        ->first();
                }
            }
            if ($isEmailExist != null || $isPhoneExist != null) {
                // if ($isEmailExist['status'] == self::STATUS_ACTIVE) {
                // $newPass = Hash::make($activeCode);
                if ($userType == 1) {
                    $account = $model->where('phone', $request['phone'])->orWhere('email', $request['email'])->first();
                    $phone_number = $account->phone;
                } else {
                    $account = $model->where('phone_number', $request['phone'])->orWhere('email', $request['email'])->first();
                    $phone_number = $account->phone_number;
                }
                $account->update([
                    'active_code' => $activeCode,
                    'password' => $activeCode,
                ]);
//                    $data = [
//                        'active_code' => $activeCode
//                    ];
                if (isset($request['email'])) {
                    $parramsMail = [
                        'send_to' => $request['email'],
                        'variable' => [
                            'data' => [
                                'name' => $account->name,
                                'phone_number' => $phone_number,
                                'email' => $account->email,
                                'password' => $activeCode
                            ],
                        ],
                        'email_type' => 6,
                    ];
                    System::sendMailNow($parramsMail);
//                        Mail::to($request['email'])->queue(new ForgotPassword($data));
                    $rs = [System::MESSAGE_SUCCESS];
                }
                $arrayParams = [
                    'phone' => $request['phone'],
                    'content' => '' . $activeCode . ' là mat khau moi cua ban de dang nhap ung dung Eroscare. Hotline 1900633613',
                ];
                $sms = self::sendSmsAgency($arrayParams);
                $rs = [System::MESSAGE_SUCCESS];
                // } else {
                //     $rs = ['error_msg'=>__('api.error_status_not_active')];
                // }
            } else {
                $rs = ['error_msg' => __('api.error_email_not_exists')];
            }
        }
        return $rs;
    }

    public static function resetPassword($request)
    {
        // $userId = self::getUserByTypeAndAccessToken($request);
        $userId = $request['user_id'];
        $arrayAccessTokenUserType = self::arrayAccessTokenUserType();
        $userType = $request->header('UserType');
        if ($request['new_password'] != $request['confirm_new_password']) {
            return ['Retype password not match'];
        }
        if (array_key_exists($userType, $arrayAccessTokenUserType)) {
            $modelUserType = $arrayAccessTokenUserType[$userType];
            $newPass = Hash::make($request['new_password']);
            $model = $modelUserType::find($userId);
            $model->password = $newPass;
            if ($model->is_vpbank_customer == 1) {
                $model->raw_password_vpbank = $request['new_password'];
            }
            $model->save();
            return $model;
        }
    }

    public static function getOldPasswordToCheck($model, $userId)
    {
        $data = $model::select('password')->where('id', $userId)->first();
        if ($data != null) {
            $array = $data->toArray();
            return $array['password'];
        }
        return '';
    }

    public static function changePassword($request)
    {
        $userId = self::getUserByTypeAndAccessToken($request);
        $arrayAccessTokenUserType = self::arrayAccessTokenUserType();
        $userType = $request->header('UserType');
        if (array_key_exists($userType, $arrayAccessTokenUserType)) {
            $modelUserType = $arrayAccessTokenUserType[$userType];
            $oldPasswordToCheck = self::getOldPasswordToCheck($modelUserType, $userId);
            if ($oldPasswordToCheck != '') {
                $check = Hash::check($request['old_password'], $oldPasswordToCheck);
                if ($check == false) {
                    return ['error_msg' => 'Mật khẩu cũ không đúng'];
                }
            } else {
                return ['error_msg' => 'Người dùng không tồn tại'];
            }
            if ($request['new_password'] != $request['confirm_new_password']) {
                return ['error_msg' => 'Mật khẩu gõ lại không khớp'];
            }
            // $newPass = Hash::make($request['new_password']);
            $model = $modelUserType::find($userId);
            $model->password = $request['new_password'];
            if ($model->is_vpbank_customer == 1) {
                $model->raw_password_vpbank = $request['new_password'];
            }
            $model->save();
            return $model;
        }
        return ['error'];
    }


    public static function validateUpdateEmail($request, $userId, $modelUserType)
    {
        $table = $modelUserType->getTable();
        $validator = Validator::make($request->all(), [
            //'email' => 'required|email|unique:customers'
            'email' => 'required|email:' . $table . ',email,' . $userId
        ]);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $msg = $messages->all();
            if (!empty($msg[0])) {
                $msg = $msg[0];
            } else {
                $msg = '';
            }
            return ['error_msg' => $msg];
        }
        return [];//return empty if validate success
    }

    public static function validateCreateEmail($request, $table)
    {
        $msgValidate = ['email.unique' => __('api.error_email_exists')];
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:' . $table
        ], $msgValidate);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $msg = $messages->all();
            return $msg[0];
        }
        return [];//return empty if validate success
    }

    public static function updateUserInOauthAccessToken($request, $userId)
    {
        $authorization = $request->header('Authorization');
        $bear = self::env('TOKEN_BEARER_HEADER_NAME');
        $accessToken = str_replace($bear . ' ', '', $authorization);
        $userType = $request->header('UserType');
        $convertTime = date('Y-m-d H:i:s', strtotime('+1 year', strtotime(date('Y-m-d'))));
        $arraySave = [
            'expires' => $convertTime,
            'user_id' => $userId
        ];
        OauthAccessToken::where('access_token', $accessToken)->where('user_type', $userType)
            ->update($arraySave);
    }

    public static function updateProfile($request)
    {
        DB::beginTransaction();
        $userId = self::getUserByTypeAndAccessToken($request);
        // $userId = $request['user_id'];
        $arrayAccessTokenUserType = self::arrayAccessTokenUserType();
        $userType = $request->header('UserType');
        if (array_key_exists($userType, $arrayAccessTokenUserType)) {
            $modelUserType = $arrayAccessTokenUserType[$userType];
            $model = $modelUserType::find($userId);
            $check_email_exits = $modelUserType->whereNotIn('email', [$model['email']])->where('email', $request['email'])->exists();
            if ($check_email_exits == true) {
                return ['error_msg' => 'Email đã tồn tại, vui lòng nhập email khác!'];
            }
            if ($userType == System::TYPE_CUSTOMER) {
                //validate phone_number and email unique
                $rs = Customer::validateCustomerInfoUnique($request->all(), 'update', $userId);
            } else {//agency
                $rs = self::validateUpdateEmail($request, $userId, $modelUserType);
            }
            if (empty($rs)) {//update
                $model->name = !empty($request['name']) ? $request['name'] : $model->name;
                $model->email = $request['email'];
                $model->address = $request['address'];
                if (isset($request['sex'])) {
                    $model->sex = $request['sex'];
                }
                if ($userType == System::TYPE_CUSTOMER) {
                    $model->phone_number = !empty($request['phone_number']) ? $request['phone_number'] : $model->phone_number;
                    $model->identity_card = !empty($request['identity_card']) ? $request['identity_card'] : '';
                }
                if (!empty($request['img_code_after'])) {
                    $model->img_code_after = $request['img_code_after'];
                }
                if (!empty($request['img_code_before'])) {
                    $model->img_code_before = $request['img_code_before'];
                }
                if (!empty($request['avatar'])) {
                    $result = Image::uploadFileViaApi($request['avatar'], $request);
                    if ($result['success'] != false) {
                        $id_image = isset($result['image']['id']) ? (int)$result['image']['id'] : 0;
                        $avatar = [
                            'image_url' => isset($result['image']['image_url']) ? $result['image']['image_url'] : '',
                            'medium_url' => isset($result['image']['medium_url']) ? $result['image']['medium_url'] : '',
                            'small_url' => isset($result['image']['small_url']) ? $result['image']['small_url'] : '',
                        ];
                        $model->image_id = $id_image;
                        $model->avatar = json_encode($avatar);
                    }
                } else {//if not send avatar but send image_id
                    if (!empty($request['image_id'])) {
                        $imageId = $request['image_id'];
                        $imageData = Image::select('image_url', 'medium_url', 'small_url')
                            ->where('id', $imageId)
                            ->first();
                        $avatarUpdate = null;
                        if ($imageData != null) {
                            $avatarUpdate = json_encode($imageData);
                        }
                        $model->avatar = $avatarUpdate;
                    }
                }
                //check image app
                if (!empty($request['image_id'])) {
                    $model->image_id = $request['image_id'];
                }
                $model->save();
                //self::updateUserInOauthAccessToken($request, $userId);
                DB::commit();
                if (!empty($model->avatar)) {
                    $avatar = json_decode($model->avatar, true);
                    if (!empty($avatar)) {
                        $model->avatar = $avatar;
                        if (!empty($avatar[0])) {
                            $model->avatar = $avatar[0];
                        }
                    }
                }
                $model->user_type = $userType;
                if ($userType == System::TYPE_AGENCY) {
                    $revenueSurplus = Agency::getRevenueAndSurplusForagency($userId);
                    $model->revenue = $revenueSurplus['revenue'] > 0 ? number_format($revenueSurplus['revenue']) . ' VNĐ' : 0;
                    $model->surplus = $revenueSurplus['surplus'] > 0 ? number_format($revenueSurplus['surplus']) . ' VNĐ' : 0;
                }
                return $model;
            } else {//return error
                DB::rollBack();
                return ['error_msg' => $rs];
            }
        }
        return [];
    }

    public static function checkPassCode($request)
    {
        $arrayAccessTokenUserType = self::arrayAccessTokenUserType();
        $userType = $request['user_type'];
        if (array_key_exists($userType, $arrayAccessTokenUserType)) {
            $modelUserType = $arrayAccessTokenUserType[$userType];
            $passTemp = 'pass_temp';
            $passTempHash = Hash::make($passTemp);
            $model = $modelUserType::where('active_code', $request['active_code'])
                ->where('email', $request['email'])
                ->first();
            if ($model != null) {
                $model->password = $passTempHash;
                $model->save();
                $userData = [$model->id => ['password' => $passTemp]];
                $request['username'] = $model->id;
                $request['password'] = $passTemp;
                $rs = OauthAccessToken::generateToken($request, $userData, $model->id);

                if (array_key_exists('oauth', $rs)) {
                    $rsConvert = $rs['oauth'];
                    $rsConvert['appKeyCode'] = $rs['appKeyCode'];
                    return $rsConvert;
                }
                return $rs;
            }

        }
        return ['error_msg' => __('api.error_pass_code')];
    }

    public static function validateDateOfBirth($date)
    {
        if (preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $date)) {
            return [];
        } else {
            return [__('api.date_not_correct_form')];
        }
    }

    /**
     * Send contact email
     */
    public static function sendContactEmail($params)
    {
        Mail::to(env('EMAIL_SEND_CONTACT'))->queue(new Contact($params));
        return [1];
    }

}
