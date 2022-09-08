<?php

namespace App\Models;

use App\Models\System;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Nexmo\Laravel\Facade\Nexmo;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Mail\AgencyCreate;
use App\Models\ProductLevelCommission;

class Agency extends Model
{
    protected $table = 'insurance_agencies';
    protected $fillable = [
        'name', 'phone', 'parent_id', 'level_id', 'email', 'username', 'password', 'description', 'address', 'avatar', 'code_agency', 'manager_id', 'commission_id', 'agency_company_id', 'agency_company_is_manager', 'created_by', 'updated_by', 'province_id', 'district_id', 'ward_id', 'type', 'code', 'bank_account_number', 'bank_account_name', 'bank_name', 'started_at', 'img_code_before', 'img_code_after', 'sale_type_id', 'personal_revenue_by_month', 'branch_revenue_by_month', 'child_agency', 'child_f1', 'child_id_f1', 'revenue_month', 'parent_number', 'birthday'
    ];

    const COMMISSION_PAID = 1;
    const COMMISSION_NOT_PAID = 0;

    public function commission_levels()
    {
        return $this->hasMany('App\Models\InsuranceAgencyCommissionLevel', 'commission_id', 'commission_id');
    }

    public function commission_level()
    {
        return $this->commission_levels()->latest()->first();
    }

    public function insuranceContracts()
    {
        return $this->hasMany('App\Models\InsuranceContract', 'sale_type_id');
    }

    public function level()
    {
        return $this->belongsTo('App\Models\Level', 'level_id');
    }

    public function revenue()
    {
        return $this->hasOne('App\Models\Revenue', 'isurance_agency_id');
    }

    public function revenueMonthlys()
    {
        return $this->hasMany('App\Models\RevenueMonthly');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\InsuranceCompany', 'agency_company_id');
    }

    public function agencyWallet()
    {
        return $this->hasOne('App\Models\AgencyWallet', 'id_agencies');
    }

    public function agencyWalletExchange()
    {
        return $this->hasMany('App\Models\AgencyWalletExchange', 'id_agencies');
    }

    public function revenueContracts()
    {
        return $this->hasMany('App\Models\RevenueContract');
    }

    public function setPasswordAttribute($pass)
    {
        $this->attributes['password'] = Hash::make($pass);
    }

    public function getPersonalRevenueByMonthAttribute()
    {
        $personal_revenue_by_month = 0;
        $first_month = date('Y-m-01 00:00:01');
        foreach ($this->insuranceContracts as $key) {
            if (Product::find($key->product_id)->is_agency != 0 && $key->updated_at >= $first_month) {
                $personal_revenue_by_month += $key->require_pay_amount;
            }
        }
        return $personal_revenue_by_month;
    }

    public function getChildAgencyAttribute()
    {
        $contract_branch = array();
        $record_cap_7 = InsuranceAgency::where('parent_id', $this->id)->get();
        foreach ($record_cap_7 as $key7) {
            if ($key7) {
                array_push($contract_branch, $key7->id);
            }

            // Thêm
            if ($key7->id != 0) {
                $record_cap_6 = InsuranceAgency::where('parent_id', $key7->id)->get();
                foreach ($record_cap_6 as $key6) {
                    if ($key6) {
                        array_push($contract_branch, $key6->id);
                    }

                    if ($key6->id != 0) {
                        $record_cap_5 = InsuranceAgency::where('parent_id', $key6->id)->get();
                        foreach ($record_cap_5 as $key5) {
                            if ($key5) {
                                array_push($contract_branch, $key5->id);
                            }
                            // Thêm

                            if ($key5->id != 0) {
                                $record_cap_4 = InsuranceAgency::where('parent_id', $key5->id)->get();
                                foreach ($record_cap_4 as $key4) {
                                    if ($key4) {
                                        array_push($contract_branch, $key4->id);
                                    }

                                    if ($key4->id != 0) {
                                        $record_cap_3 = InsuranceAgency::where('parent_id', $key4->id)->get();
                                        foreach ($record_cap_3 as $key3) {
                                            if ($key3) {
                                                array_push($contract_branch, $key3->id);
                                            }

                                            if ($key3->id != 0) {
                                                $record_cap_2 = InsuranceAgency::where('parent_id', $key3->id)->get();
                                                foreach ($record_cap_2 as $key2) {
                                                    if ($key2) {
                                                        array_push($contract_branch, $key2->id);
                                                    }

                                                    if ($key2->id != 0) {
                                                        $record_cap_1 = InsuranceAgency::where('parent_id', $key2->id)->get();
                                                        foreach ($record_cap_1 as $key1) {
                                                            if ($key1) {
                                                                array_push($contract_branch, $key1->id);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }


                        }//thêm
                    }//thêm
                }//thêm
            }//thêm
        }
        return json_encode($contract_branch);
    }

    public function parent_number($parent_id, $i = 0)
    {
        if ($parent_id != 0) {
            $i = $i + 1;
            $parent_id = Agency::find($parent_id)->parent_id;
            $i = $this->parent_number($parent_id, $i);
        }
        return $i;
    }

    public function getParentNumberAttribute()
    {
        return $this->parent_number($this->parent_id);
    }

    public function getChildF1Attribute()
    {
        $contract_branch = array();
        $record_cap_5 = InsuranceAgency::where('parent_id', $this->id)->get();
        return $record_cap_5;
    }

    public function getChildIdF1Attribute()
    {
        $contract_branch = array();
        $record_cap_5 = InsuranceAgency::where('parent_id', $this->id)->get();
        foreach ($record_cap_5 as $key5) {
            if ($key5) {
                array_push($contract_branch, $key5->id);
            }
        }
        return $contract_branch;
    }

    /**
     * @param $id
     * @return Model|null|static
     */
    public static function getDetail($id)
    {
        return self::where('id', $id)->first();
    }

    /**
     * Get comission amount by product
     *
     * @param $agencyId
     * @param $productId
     * @return int|mixed
     */
    public static function getCommissionAmountByProduct($agencyId, $productId)
    {
        $agency = self::find($agencyId);
        $agencyCommissionLevel = $agency->commission_level();
        if (!empty($agencyCommissionLevel)) {
            // Get commission amount by product
            $productCommission = ProductLevelCommission::where('product_id', $productId)
                ->where('commission_id', $agencyCommissionLevel->commission_id)->latest();
            if (!empty($productCommission)) {
                $productCommission = $productCommission->first();
                if (!empty($productCommission)) {
                    return $productCommission->commission_amount;
                }
            }
        }

        return 0;
    }

    /**
     * Check agency is company
     * If agency is company, get all data from this company's staff
     */
    public static function checkAgencyIsCompany($agencyId)
    {
        $agencyInfo = Agency::find($agencyId);
        if (!empty($agencyInfo)) {
            if ($agencyInfo->agency_company_is_manager == 1) {
                $arrayAgencyIdStaff = Agency::select('id')
                    ->where('agency_company_id', $agencyId)
                    ->get()
                    ->toArray();
                $arrayAgencyIdStaffConvert = [];
                if (!empty($arrayAgencyIdStaff)) {
                    foreach ($arrayAgencyIdStaff as $row) {
                        $arrayAgencyIdStaffConvert[] = $row['id'];
                    }
                }
                array_push($arrayAgencyIdStaffConvert, (int)$agencyId);
                return $arrayAgencyIdStaffConvert;
            } else {
                $arrayAgencyIdStaffConvert = [(int)$agencyId];
                return $arrayAgencyIdStaffConvert;
            }
        } else {
            return [];
        }
    }

    // public static function sendSmsAgency()
    // {
    //     Nexmo::message()->send([
    //         'to'   => $phone_number,
    //         'from' => 'xxxxxx',
    //         'text' => 'Text'
    //     ]);
    // }
    public static function checkAge($date)
    {
        if (strpos($date, '/') !== false) {
            // if string contains / -> date has format : dd/mm/YYY
            $dateArray = explode('/', $date);
            $dataConvert = $dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0];
            return $dataConvert;
        }
        $birthDate = $date;
        $today = date("Y-m-d");
        //explode the date to get month, day and year
        //get age from date or birthdate
        $diff = strtotime($today) - strtotime($birthDate);

        $years = floor($diff / (365 * 60 * 60 * 24));
        if ($years < 16 || $years > 99) {
            return false;
        } else {
            return $date;
        }
    }

    public static function register($request, $activeCode)
    {
//        $passwordRandom = Rand ( 10000000 , 99999999 );
        $passwordRandom = 'moncover';
        // check co ma gioi thieu hay khong
        if (isset($request->referral_code)) {
            $manager_id = '1685';
        } else {
            if (!empty($request['parent_id'])) {
                $check_agency = Agency::find($request['parent_id']);
                if ($check_agency->manager_id != 0 && $check_agency->manager_id != '') {
                    $manager_id = $check_agency->manager_id;
                } else {
                    $manager_id = '';
                }
            } else {
                $manager_id = 0;
            }
        }
        do {
            $invitation_code = str_random(8);
            $agency = Agency::where('code_agency', $invitation_code)->first();
        } while (!empty($agency));
        if (isset($request['email']) && $request->has('email')) {
            $check_email_agency = Agency::where('email', $request['email'])->exists();
            if ($check_email_agency == true) {
                // return ['error_msg' => 'Email đã tồn tại'];
                return ['success' => false, 'message' => 'Email đã tồn tại'];
            }
        }
        if ($request->has('phone')) {
            $check_phone_agency = Agency::where('phone', $request->phone)->exists();
            if ($check_phone_agency == true) {
                // return ['error_msg' => 'Số điện thoại đã tồn tại'];
                return ['error_msg' => 'Số điện thoại đã tồn tại vui lòng kiểm tra lại số hoặc thành viên nay đã gia nhập hệ thống'];
            }
        }
        // if($request['name'] == null){
        //     return ['success' => false, 'message' => 'Thiếu trường tên'];
        // }
        if ($request['phone'] == null) {
            return ['error_msg' => 'Thiếu trường số điện thoại'];
        }
        // if($request['province_id'] == null){
        //     return ['success' => false, 'message' => 'Thiếu trường tỉnh, thành phố'];
        // }
        // if($request['type'] == null){
        //     return ['success' => false, 'message' => 'Thiếu trường loại đại lý'];
        // }
        else {
            if (isset($request->referral_code)) {
                $insurance_agency = new Agency;
                $insurance_agency->name = Customer::checkName($request['name']);
                if ($insurance_agency->name == false) {
                    return ['error_msg' => 'Tên khách hàng có ít nhất 2 từ, mỗi từ có ít nhất 2 ký tự'];
                }
                $insurance_agency->email = isset($request["email"]) ? $request["email"] : '';
                $insurance_agency->phone = $request['phone'];;
                if (strlen($insurance_agency->phone) != 10 || !is_numeric($insurance_agency->phone)) {
                    return ['error_msg' => 'Số điện thoại không chính xác'];
                }
                $insurance_agency->password = 'moncover';
                $insurance_agency->avatar = isset($request["avatar"]) ? $request["avatar"] : '';
                $insurance_agency->img_code_before = isset($request["img_code_before"]) ? $request["img_code_before"] : '';
                $insurance_agency->img_code_after = isset($request["img_code_after"]) ? $request["img_code_after"] : '';
                $insurance_agency->address = isset($request["address"]) ? $request["address"] : '';
                $insurance_agency->description = isset($request["description"]) ? $request["description"] : '';
                $insurance_agency->birthday = self::checkAge($request['birthday']);
                if ($insurance_agency->birthday == false) {
                    return ['error_msg' => 'Độ tuổi của khách hàng nằm trong khoảng 16-99'];
                }
                $insurance_agency->level_id = 1;
                $insurance_agency->parent_id = $request->referral_code;
                $insurance_agency->manager_id = $manager_id;
                $insurance_agency->agency_company_id = null;
                $insurance_agency->agency_company_is_manager = null;
                $insurance_agency->commission_id = null;
                $insurance_agency->province_id = isset($request["province_id"]) ? $request["province_id"] : null;
                $insurance_agency->district_id = isset($request["district_id"]) ? $request["district_id"] : null;
                $insurance_agency->ward_id = isset($request["ward_id"]) ? $request["ward_id"] : null;
                $insurance_agency->bank_account_number = isset($request["bank_account_number"]) ? $request["bank_account_number"] : '';
                $insurance_agency->bank_account_name = isset($request["bank_account_name"]) ? $request["bank_account_name"] : '';
                $insurance_agency->bank_name = isset($request["bank_name"]) ? $request["bank_name"] : '';
                $insurance_agency->started_at = Carbon::now();
                $insurance_agency->created_by = $request['created_by'];
                $insurance_agency->updated_by = $request['agency_id'];
                $insurance_agency->type = isset($request["type"]) ? $request["type"] : 0;
                $insurance_agency->code = isset($request["code"]) ? $request["code"] : '';
                $insurance_agency->created_id = 0;
                $insurance_agency->code_agency = $invitation_code;
                $insurance_agency->active_code = $activeCode;
                $insurance_agency->save();

                //send sms
                if (isset($request['password'])) {
                    $arrayParams = [
                        'phone' => $request['phone'],
                        'password' => $request['password'],
                        'content' => 'Tài khoản đối tác ' . $insurance_agency['id'] . ' đã được tạo trên hệ thống Moncover. Mật khẩu đăng nhập ' . $request['password'] . '. Vui lòng tải ứng dụng http://apps.eroscare.com để tiếp tục. Chúc bạn thành công',
                    ];
                } else {
                    $arrayParams = [
                        'phone' => $request['phone'],
                        'password' => $request['password'],
                        'content' => 'Tài khoản đối tác ' . $insurance_agency['id'] . ' đã được tạo trên hệ thống Moncover. Mật khẩu đăng nhập "moncover". Vui lòng tải ứng dụng http://apps.eroscare.com để tiếp tục. Chúc bạn thành công',
                    ];
                }
//                $sms = System::sendSmsAgency($arrayParams);
                // send mail
                $agency_id = $insurance_agency['id'];
                $passwordOrigin = $request['password'];
                $result = $request->all();

                if (!empty($insurance_agency['parent_id'])) {
                    System::pushNotificationByFcm([
                        'title' => 'Tạo đại lý thành công',
                        'message' => 'Tài khoản đối tác ' . $insurance_agency['id'] . ' đã được tạo trên hệ thống Moncover. Mật khẩu đăng nhập "moncover". Vui lòng tải ứng dụng http://apps.moncover.vn để tiếp tục. Chúc bạn thành công',
                        'user_id' => $insurance_agency['parent_id'],
                        'user_type' => 1
                    ]);
                }

//                if(isset($request["email"])){
//                    // send mail tạo đại lý cấp dưới email 14
//                    $parramsMail = [
//                        'send_to' => $insurance_agency->email,
//                        'variable' => [
//                            'data' => ['name' => $insurance_agency->name,'id' => $insurance_agency->id, 'email' => $insurance_agency->email],
//                            'passwordOrigin' => isset($request["password"]) ? $request["password"] : 'eroscare'
//                        ],
//                        'email_type' => 14,
//                    ];
//                    System::sendMailQueue($parramsMail);
//
//                    // send mail hướng dẫn sử dụng email 3
//                    $parramsMail2 = [
//                        'send_to' => $insurance_agency->email,
//                        'variable' => [
//                            'data' => ['name' => $insurance_agency->name],
//                        ],
//                        'email_type' => 3,
//                    ];
//                    System::sendMailQueue($parramsMail2);
//
////                    Mail::to($request["email"])->send(new AgencyCreate($result, $passwordOrigin, $agency_id));
//                }
                return $insurance_agency;
            } else {
                if (!empty($request['parent_id'])) {
                    $insurance_agency = new Agency;
                    $insurance_agency->name = Customer::checkName($request['name']);
                    if ($insurance_agency->name == false) {
                        return ['error_msg' => 'Tên khách hàng có ít nhất 1 từ, mỗi từ có ít nhất 1 ký tự'];
                    }
                    $insurance_agency->email = isset($request["email"]) ? $request["email"] : '';
                    $insurance_agency->phone = $request['phone'];;
                    if (strlen($insurance_agency->phone) != 10 || !is_numeric($insurance_agency->phone)) {
                        return ['error_msg' => 'Số điện thoại không chính xác'];
                    }
                    $insurance_agency->password = 'moncover';
                    $insurance_agency->avatar = isset($request["avatar"]) ? $request["avatar"] : '';
                    $insurance_agency->img_code_before = isset($request["img_code_before"]) ? $request["img_code_before"] : '';
                    $insurance_agency->img_code_after = isset($request["img_code_after"]) ? $request["img_code_after"] : '';
                    $insurance_agency->address = isset($request["address"]) ? $request["address"] : '';
                    $insurance_agency->description = isset($request["description"]) ? $request["description"] : '';
                    $insurance_agency->birthday = self::checkAge($request['birthday']);
                    if ($insurance_agency->birthday == false) {
                        return ['error_msg' => 'Độ tuổi của khách hàng nằm trong khoảng 16-99'];
                    }
                    $insurance_agency->level_id = 1;
                    $insurance_agency->parent_id = $request->parent_id;
                    $insurance_agency->manager_id = $manager_id;
                    $insurance_agency->agency_company_id = null;
                    $insurance_agency->agency_company_is_manager = null;
                    $insurance_agency->commission_id = null;
                    $insurance_agency->province_id = isset($request["province_id"]) ? $request["province_id"] : null;
                    $insurance_agency->district_id = isset($request["district_id"]) ? $request["district_id"] : null;
                    $insurance_agency->ward_id = isset($request["ward_id"]) ? $request["ward_id"] : null;
                    $insurance_agency->bank_account_number = isset($request["bank_account_number"]) ? $request["bank_account_number"] : '';
                    $insurance_agency->bank_account_name = isset($request["bank_account_name"]) ? $request["bank_account_name"] : '';
                    $insurance_agency->bank_name = isset($request["bank_name"]) ? $request["bank_name"] : '';
                    $insurance_agency->started_at = Carbon::now();
                    $insurance_agency->created_by = $request['created_by'];
                    $insurance_agency->updated_by = $request['agency_id'];
                    $insurance_agency->type = isset($request["type"]) ? $request["type"] : 0;
                    $insurance_agency->code = isset($request["code"]) ? $request["code"] : '';
                    $insurance_agency->active_code = $activeCode;
                    $insurance_agency->created_id = 0;
                    $insurance_agency->code_agency = $invitation_code;
                    DB::beginTransaction();
                    $insurance_agency->save();
                    DB::commit();

                    //send sms
                    if (isset($request['password'])) {
                        $arrayParams = [
                            'phone' => $request['phone'],
                            'password' => $request['password'],
                            'content' => 'Tài khoản đối tác ' . $insurance_agency['id'] . ' đã được tạo trên hệ thống Eroscare. Mật khẩu đăng nhập ' . $request['password'] . '. Vui lòng tải ứng dụng http://apps.eroscare.com để tiếp tục. Chúc bạn thành công',
                        ];
                    } else {
                        $arrayParams = [
                            'phone' => $request['phone'],
                            'password' => $request['password'],
                            'content' => 'Tài khoản đối tác ' . $insurance_agency['id'] . ' đã được tạo trên hệ thống Eroscare. Mật khẩu đăng nhập "moncover". Vui lòng tải ứng dụng http://apps.eroscare.com để tiếp tục. Chúc bạn thành công',
                        ];
                    }
//                    $sms = System::sendSmsAgency($arrayParams);

                    // send mail
                    $agency_id = $insurance_agency['id'];
                    $passwordOrigin = $request['password'];
                    $result = $request->all();
                    if (!empty($insurance_agency['parent_id'])) {
                        System::pushNotificationByFcm([
                            'title' => 'Tạo đại lý thành công',
                            'message' => 'Tài khoản đối tác ' . $insurance_agency['id'] . ' đã được tạo trên hệ thống Moncover. Mật khẩu đăng nhập "moncover". Vui lòng tải ứng dụng http://apps.moncover.vn để tiếp tục. Chúc bạn thành công',
                            'user_id' => $insurance_agency['parent_id'],
                            'user_type' => 1
                        ]);
                    }

//                    if(isset($request->email)){
//
//                        // send mail tạo đại lý cấp dưới email 14
//                        $parramsMail = [
//                            'send_to' => $insurance_agency->email,
//                            'variable' => [
//                                'data' => ['name' => $insurance_agency->name,'id' => $insurance_agency->id, 'email' => $insurance_agency->email],
//                                'passwordOrigin' => isset($request["password"]) ? $request["password"] : 'eroscare'
//                            ],
//                            'email_type' => 14,
//                        ];
//                        System::sendMailQueue($parramsMail);
//
//                        // send mail hướng dẫn sử dụng email 3
//                        $parramsMail2 = [
//                            'send_to' => $insurance_agency->email,
//                            'variable' => [
//                                'data' => ['name' => $insurance_agency->name],
//                            ],
//                            'email_type' => 3,
//                        ];
//                        System::sendMailQueue($parramsMail2);
//
////                        Mail::to($request->email)->send(new AgencyCreate($result, $passwordOrigin, $agency_id));
//                    }
                    return $insurance_agency;
                } else {
                    $insurance_agency = new Agency;
                    $insurance_agency->name = Customer::checkName($request['name']);
                    if ($insurance_agency->name == false) {
                        return ['error_msg' => 'Tên khách hàng có ít nhất 2 từ, mỗi từ có ít nhất 2 ký tự'];
                    }
                    $insurance_agency->email = isset($request["email"]) ? $request["email"] : '';
                    $insurance_agency->phone = $request['phone'];;
                    if (strlen($insurance_agency->phone) != 10 || !is_numeric($insurance_agency->phone)) {
                        return ['error_msg' => 'Số điện thoại không chính xác'];
                    }
                    $insurance_agency->password = 'moncover';
                    $insurance_agency->avatar = isset($request["avatar"]) ? $request["avatar"] : '';
                    $insurance_agency->img_code_before = isset($request["img_code_before"]) ? $request["img_code_before"] : '';
                    $insurance_agency->img_code_after = isset($request["img_code_after"]) ? $request["img_code_after"] : '';
                    $insurance_agency->address = isset($request["address"]) ? $request["address"] : '';
                    $insurance_agency->description = isset($request["description"]) ? $request["description"] : '';
                    $insurance_agency->birthday = self::checkAge($request['birthday']);
                    if ($insurance_agency->birthday == false) {
                        return ['error_msg' => 'Độ tuổi của khách hàng nằm trong khoảng 16-99'];
                    }
                    $insurance_agency->level_id = 1;
                    $insurance_agency->parent_id = 1310;
                    $insurance_agency->manager_id = $manager_id;
                    $insurance_agency->agency_company_id = null;
                    $insurance_agency->agency_company_is_manager = null;
                    $insurance_agency->commission_id = null;
                    $insurance_agency->province_id = isset($request["province_id"]) ? $request["province_id"] : null;
                    $insurance_agency->district_id = isset($request["district_id"]) ? $request["district_id"] : null;
                    $insurance_agency->ward_id = isset($request["ward_id"]) ? $request["ward_id"] : null;
                    $insurance_agency->bank_account_number = isset($request["bank_account_number"]) ? $request["bank_account_number"] : '';
                    $insurance_agency->bank_account_name = isset($request["bank_account_name"]) ? $request["bank_account_name"] : '';
                    $insurance_agency->bank_name = isset($request["bank_name"]) ? $request["bank_name"] : '';
                    $insurance_agency->started_at = Carbon::now();
                    $insurance_agency->created_by = $request['created_by'];
                    $insurance_agency->updated_by = $request['agency_id'];
                    $insurance_agency->type = isset($request["type"]) ? $request["type"] : 0;
                    $insurance_agency->code = isset($request["code"]) ? $request["code"] : '';
                    $insurance_agency->created_id = 0;
                    $insurance_agency->active_code = $activeCode;
                    $insurance_agency->code_agency = $invitation_code;
                    $insurance_agency->save();
                    //send sms
                    if (isset($request['password'])) {
                        $arrayParams = [
                            'phone' => $request['phone'],
                            'password' => $request['password'],
                            'content' => 'Tài khoản đối tác ' . $insurance_agency['id'] . ' đã được tạo trên hệ thống Eroscare. Mật khẩu đăng nhập ' . $request['password'] . '. Vui lòng tải ứng dụng http://apps.eroscare.com để tiếp tục. Chúc bạn thành công',
                        ];
                    } else {
                        $arrayParams = [
                            'phone' => $request['phone'],
                            'password' => $request['password'],
                            'content' => 'Tài khoản đối tác ' . $insurance_agency['id'] . ' đã được tạo trên hệ thống Eroscare. Mật khẩu đăng nhập "moncover". Vui lòng tải ứng dụng http://apps.eroscare.com để tiếp tục. Chúc bạn thành công',
                        ];
                    }
//                    $sms = System::sendSmsAgency($arrayParams);
                    // send mail
                    $agency_id = $insurance_agency['id'];
                    $passwordOrigin = $request['password'];
                    $result = $request->all();

                    if (!empty($insurance_agency['parent_id'])) {
                        System::pushNotificationByFcm([
                            'title' => 'Tạo đại lý thành công',
                            'message' => 'Tài khoản đối tác ' . $insurance_agency['id'] . ' đã được tạo trên hệ thống Moncover. Mật khẩu đăng nhập "moncover". Vui lòng tải ứng dụng http://apps.moncover.vn để tiếp tục. Chúc bạn thành công',
                            'user_id' => $insurance_agency['parent_id'],
                            'user_type' => 1
                        ]);
                    }
//                    if(isset($request->email)){
//
//                        // send mail tạo đại lý cấp dưới email 14
//                        $parramsMail = [
//                            'send_to' => $insurance_agency->email,
//                            'variable' => [
//                                'data' => ['name' => $insurance_agency->name,'id' => $insurance_agency->id, 'email' => $insurance_agency->email],
//                                'passwordOrigin' => isset($request["password"]) ? $request["password"] : 'eroscare'
//                            ],
//                            'email_type' => 14,
//                        ];
//                        System::sendMailQueue($parramsMail);
//
//                        // send mail hướng dẫn sử dụng email 3
//                        $parramsMail2 = [
//                            'send_to' => $insurance_agency->email,
//                            'variable' => [
//                                'data' => ['name' => $insurance_agency->name],
//                            ],
//                            'email_type' => 3,
//                        ];
//                        System::sendMailQueue($parramsMail2);
//
////                        Mail::to($request->email)->send(new AgencyCreate($result, $passwordOrigin, $agency_id));
//                    }
                    return $insurance_agency;
                }
            }
        }
    }

    public static function getRevenueAndSurplusForagency($agencyId)
    {
        $agencyIdArray = self::checkAgencyIsCompany($agencyId);
        $revenue = Contract::whereIn('sale_type_id', $agencyIdArray)->sum('require_pay_amount');
        $surplus = Contract::whereIn('sale_type_id', $agencyIdArray)
            ->where('commission_pay', self::COMMISSION_NOT_PAID)
            ->sum('commission_sale_amount');
        return ['revenue' => $revenue, 'surplus' => $surplus];
    }

    /**
     * Return profile
     */
    public static function getProfile($id)
    {
        $arrayField = [
            'id', 'name', 'email', 'address', 'avatar', 'code', 'type'
        ];
        $data = self::select($arrayField)->where('id', $id)->first();
        if ($data != null) {
            $data['user_type'] = System::TYPE_AGENCY;
            $rs = self::getRevenueAndSurplusForagency($id);
            $data['revenue'] = $rs['revenue'] > 0 ? number_format($rs['revenue']) : 0;
            $data['surplus'] = $rs['surplus'] > 0 ? number_format($rs['surplus']) : 0;
            return $data;
        }
        return [];
    }

    /**
     * Return agency commission and history paid
     */
    public static function getCommission($request)
    {
        $token = $request->token;
        $userId = $token['user_id'];
        $agencyIdArray = self::checkAgencyIsCompany($userId);
        !empty($request->page) ? $page = $request->page : $page = 1;
        !empty($request->page_size) ? $limit = $request->page_size : $limit = 10;
        $offset = ($page * $limit) - $limit;
        $productTable = with(new Product())->getTable();
        $contractTable = with(new Contract())->getTable();
        $customerTable = with(new Customer())->getTable();
        $arrayField = [
            'co.id', 'co.product_code', 'co.commission_sale_amount', 'co.created_at', 'co.commission_pay', 'co.commision_pay_date',
            'p.name AS product_name', 'c.name AS customer_name', 'co.type_id as insurance_type_id'
        ];
        $query = DB::table($contractTable . ' AS co')
            ->leftJoin($productTable . ' AS p', 'co.product_id', '=', 'p.id')
            ->leftJoin($customerTable . ' AS c', 'co.customer_id', '=', 'c.id')
            ->select($arrayField)
            ->whereIn('co.sale_type_id', $agencyIdArray);
        if (isset($request->type)) {
            $query->where('co.commission_pay', $request->type);
        }
        if (!empty($request->start_date)) {
            $startDate = System::convertDateToStandard($request->start_date);
            $query->whereDate('co.created_at', '>=', $startDate);
        }
        if (!empty($request->end_date)) {
            $endDate = System::convertDateToStandard($request->end_date);
            $query->whereDate('co.created_at', '<=', $endDate);
        }
        $data = $query->offset($offset)
            ->limit($limit)
            ->orderBy('co.created_at', 'desc')
            ->get();
        $rs = [
            'count' => $query->count(),
            'data' => $data
        ];
        return $rs;
    }
}
