<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Http\Controllers\DashboardController;
use App\Emails\AgencyCreate;
use App\Jobs\NotifyContractProvideSuccess;
use App\Lib\ExportContractHelper;
use App\Lib\InsuranceContractService;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductCommission;
// use Modules\Product\Models\InsuranceType;
use Modules\Product\Models\ProductCustomerCommission;
use App\Models\InsuranceAgency;
use App\Models\GetDataFromAPI;
use App\Models\Flight;
use phpseclib\Crypt\RSA;
use App\Models\MailQueue;

class InsuranceContractPartner extends Model
{
    protected $fillable = [
        'insurance_quotation_id','code','contract_number', 'customer_id','filter_data', 'product_id', 'product_code', 'main_fee', 'selected_price_type', 'extra_products', 'extra_product_filter_data', 'extra_product_filter_data', 'extra_fee_attributes', 'product_price','type_id', 'addition_attributes', 'description', 'note', 'gross_amount','vat','net_amount', 'discount_amount','discount_amount_of_insurance_company','discount_amount_of_insurance_company_type', 'discount_type', 'require_pay_amount', 'paid_amount', 'start_time','end_time','sale_type','sale_type_id', 'commission_product','commission_sale', 'commission_sale_amount', 'commission_customer', 'commission_customer_amount', 'status', 'renewals_customer_status', 'renewals_number_contract', 'payment_status', 'notify_provide_contract', 'customer_detail','renewal_number', 'provide_service', 'updated_by', 'created_by', 'certificate_active','get_file_times','commission_pay', 'commision_pay_date','commistion_pay_created_id','subsidiaries', 'url_cerficate', 'url_cerficate_zip', 'cerficate', 'created_id', 'created_type', 'coupon_code'
    ];

    protected $guarded = [];

    const COMMISSION_NOT_PAID = 0;
    const COMMISSION_PAID = 1;
    /**
     * Relationship
     */
    public function insurance_type()
    {
        return $this->belongsTo('App\Models\InsuranceType', 'type_id');
    }
    public function product()
    {
        return $this->belongsTo('Modules\Product\Models\Product');
    }
    public function customer()
    {
        return $this->belongsTo('App\Models\Customer');
    }
    public function files()
    {
        return $this->hasMany('App\Models\InsuranceContractFile', 'contract_id');
    }

    public function payments()
    {
        return $this->hasMany('App\Models\InsuranceContractPayment','contract_id');
    }

    public function agences ()
    {
        return $this->belongsTo('App\Models\InsuranceAgency','sale_type_id');
    }

    public function beneficiary()
    {
        return $this->hasMany('App\Models\InsuranceContractBeneficiary','contract_id');
    }

    public function users()
    {
        return $this->belongsTo('Modules\Core\Models\User','sale_type_id');
    }

    public function subsidiary()
    {
        return $this->hasOne(Company::class, 'id','subsidiaries');
    }
   

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_DELETED = -1;
    const STATUS_CANCEL = -2;

    public function getStatusName() {
        if ($this->status == self::STATUS_ACTIVE) {
            return "Có hiệu lực";
        } elseif ($this->status == self::STATUS_INACTIVE) {
            if ($this->certificate_active == 0) {
                return 'Đã cấp đơn, chưa hiệu lực';
            } else {
                return "Chưa hiệu lực";
            }
        }
        else
            return "Đã hủy";
    }

    const PAYMENT_STATUS_NOT = 0;
    const PAYMENT_STATUS_IN_PROGRESS = 2;
    const PAYMENT_STATUS_COMPLETED = 1;
    const PAYMENT_STATUS_VNPAY_ERROR = 5;
    const PAYMENT_STATUS_COMPLETED_CTBH = 3;

    const NOTIFY_PROVIDE_SUCCESS = 1;
    const NOTIFY_PROVIDE_WAIT = 0;
    
    /**
     * Get payment status name
     * @return string
     */
    public function getPaymentStatusName() {
        if ($this->payment_status == self::PAYMENT_STATUS_NOT)
            return "Chưa thanh toán";
        elseif ($this->payment_status == self::PAYMENT_STATUS_IN_PROGRESS)
            return "Đang thanh toán";
        else if ($this->payment_status == self::PAYMENT_STATUS_COMPLETED)
            return "Đã thanh toán";
        else if ($this->payment_status == self::PAYMENT_STATUS_VNPAY_ERROR)
            return "Xảy ra lỗi khi thanh toán qua VNPAY";
        else
            return "Đã thanh toán CTBH";
    }

    /**
     * @return string
     */
    public function getPaymentStatusClass()
    {
        if ($this->payment_status == self::PAYMENT_STATUS_NOT) {
            return 'text-red';
        } elseif ($this->payment_status == self::PAYMENT_STATUS_IN_PROGRESS) {
            return 'text-yellow';
        }
        elseif ($this->payment_status == self::PAYMENT_STATUS_VNPAY_ERROR) {
            return 'text-red';
        }else {
            return 'text-green';
        }
    }

    const SALE_TYPE_CUSTOMER = 0;
    const SALE_TYPE_USER = 1;
    const SALE_TYPE_AGENCY = 2;

    public static function searchByCondition($params) {
        $p = InsuranceContract::with('insurance_type')->with('product')->with('customer')
            ->where('status', '>', self::STATUS_DELETED);
        if(isset($params["status"])){
            if ($params["status"] == 1){
                $p = InsuranceContract::with('insurance_type')->with('product')->with('customer')->where('status', 1);
            }
            if ($params["status"] == 0) {
                $p = InsuranceContract::with('insurance_type')->with('product')->with('customer')->where('status', 0);
            }
            if ($params["status"] == -2) {
                $p = InsuranceContract::with('insurance_type')->with('product')->with('customer')->where('status', -2);
            }
        }
        // if ($params["status"] != null && $params["status"] == 1){
        //     $p = InsuranceContract::with('insurance_type')->with('product')->with('customer')->where('status', 1);
        // }
        // if ($params["status"] != null && $params["status"] == 0) {
        //     $p = InsuranceContract::with('insurance_type')->with('product')->with('customer')->where('status', 0);
        // }
        if (!empty($params["agency_id"])) {
            $p = $p->where('sale_type_id', $params["agency_id"])->where('sale_type', self::SALE_TYPE_AGENCY);
        }
        if (!empty($params["customer_id"])) {
            $p = $p->where('customer_id', $params["customer_id"]);
        }
    
        if (!empty($params["code"])) {
            $p = $p->where('id', $params["code"]);
        }

        if (!empty($params["user_id"])) {
            $agen = InsuranceAgency::where('manager_id', $params["user_id"])->pluck('id')->toArray();
            $p = $p->whereIn('sale_type_id', $agen)->orWhere('created_by', $params["user_id"]);
        }
        
        if (!empty($params["product_id"])) {
            $p = $p->where('product_id', $params["product_id"]);
        }
        if (!empty($params["type_id"])) {
            $p = $p->where('type_id', $params["type_id"]);
        }
        if (isset($params["payment_status"]) && in_array($params["payment_status"], [0, 1, 2, 3, 4])) {
            // Chưa xác nhận thanh toán
            if($params["payment_status"] == 4){
                $p = $p->whereRaw('id IN (SELECT contract_id FROM insurance_contract_payments WHERE status = 0) AND payment_status IN (0,2)');
            }else{
                $p = $p->where('payment_status', $params["payment_status"]);
            }
        }
        if (isset($params["start_time"])) {
            $p = $p->whereDate('start_time', '>=', Carbon::createFromFormat('d/m/Y', $params['start_time'])->format('Y-m-d'))->orderBy('start_time', 'asc');
        }
        if (isset($params["end_time"])) {
            $p = $p->whereDate('end_time', '<=', Carbon::createFromFormat('d/m/Y', $params['end_time'])->format('Y-m-d'))->orderBy('start_time', 'asc');
        }
        if (isset($params["start"])) {
            $p->whereBetween('created_at', [$params["start"], $params["end"]]);
        }
        if (!empty($params['insurance_company'])) {
            $company = $params['insurance_company'];
            $p->whereHas('product', function ($query)  use ($company) {
                $query->where('company_id', $company);
            });
        }

        // if (isset($params['contract_time'])){
        //     $time = explode(' - ',$params['contract_time']);
        //     $start_time = Carbon::createFromFormat('d/m/Y', $time[0])->format('Y-m-d');
        //     $end_time = Carbon::createFromFormat('d/m/Y', $time[1])->format('Y-m-d');

        //     $p->whereBetween(DB::raw("(STR_TO_DATE(created_at,'%Y-%m-%d'))"), [$start_time, $end_time]);
        //     $p = $p->whereDate(['contract_time', '>=', Carbon::createFromFormat('d/m/Y', $params['start_time'], []])->format('Y-m-d'));
        // }
        //Tai khoan sale
        // $belong_to_role = Auth::user()->load('roles');
        // $agen = InsuranceAgency::where('manager_id', Auth::id())->pluck('id')->toArray();
        // //If sales => Chi duoc xem cac hop dong cua sale do
        // if (!empty($belong_to_role->roles[0]) && $belong_to_role->roles[0]->id == 5) {
        //     $p->whereIn('sale_type_id', $agen)->orWhere('sale_type_id',Auth::id());
        // }
        return $p->orderBy('created_at', 'desc')->paginate(50);
    }

    /**
     * @param $params
     * @param int $createId
     * @return array
     */
    public static function createContract($params, $createId = 0)
    {
        $insuranceType = InsuranceType::getDetail($params['insurance_type_id']);
        if($params['insurance_type_id'] == 23) {
            // $startTime = Carbon::createFromFormat('d/m/Y H:i', $params['start_time']);
            // $endTime = Carbon::createFromFormat('d/m/Y H:i', $params['end_time']);
            
            $startTime = date('Y/m/d H:i:s', strtotime($params['start_time']));
            $endTime = date('Y/m/d H:i:s', strtotime($params['end_time']));
        } else {
            try {
                $startTime = Carbon::createFromFormat('d/m/Y H:i', $params['start_time']);
            } catch (\Exception $ex) {
                try {
                    $startTime = Carbon::createFromFormat('d/m/Y', $params['start_time']);
                } catch (\Exception $ex) {
                    Log::error($ex->getMessage() . '. File: ' . $ex->getFile() . '. Line: ' . $ex->getLine());
    
                    return ['success' => false, 'message' => 'Kiểm tra lại định dạng ngày tháng của start time. định dạng bắt buộc: d/m/Y H:i. ' . $params['start_time']];
                }
            }
            // Get end time
            if (isset($params['end_time'])) {
                try {
                    $endTime = Carbon::createFromFormat('d/m/Y H:i', $params['end_time']);
                } catch (\Exception $ex) {
                    try {
                        $endTime = Carbon::createFromFormat('d/m/Y', $params['end_time']);
                    } catch (\Exception $ex) {
                        Log::error($ex->getMessage() . '. File: ' . $ex->getFile() . '. Line: ' . $ex->getLine());
    
                        return ['success' => false, 'message' => 'Kiểm tra lại định dạng ngày tháng của end time. định dạng bắt buộc: d/m/Y H:i. ' . $params['end_time']];
                    }
                }
            } else {
                // Get from start time
                switch ($insuranceType->fee_interval_type) {
                    case 'years':
                        if (isset($params['filter_data']['year_interval_value'])) {
                            $endTime = $startTime->copy()->addYears((int)$params['filter_data']['year_interval_value']);
                        } else {
                            return ['success' => false, 'message' => 'Thiếu tham số year_interval_value trong filter_data'];
                        }
                        break;
                }
            }
    
            if (!isset($endTime)) {
                return ['success' => false, 'message' => 'Thiếu tham số end time'];
            }
    
        //    if ($startTime->getTimestamp() < strtotime(date('Y-m-d') . " 00:00:00")) {
        //        return ['success' => false, 'message' => 'Thời gian bắt đầu phải lớn hơn thời điểm hiện tại'];
        //    }
    
            if ($startTime->getTimestamp() >= $endTime->getTimestamp()) {
                return ['success' => false, 'message' => 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu'];
            }
        }

        if($params["sale_type"] == self::SALE_TYPE_AGENCY)
            $saleTypeId = isset($params["sale_type_agency_id"]) ? $params["sale_type_agency_id"] : null;
        elseif($params["sale_type"] == self::SALE_TYPE_USER)
            $saleTypeId = isset($params["sale_type_user_id"]) ? $params["sale_type_user_id"] : null;
        else {
            $saleTypeId = 0;
        }

        if (empty($saleTypeId) && isset($params['sale_type_id'])) {
            $saleTypeId = $params['sale_type_id'];
        }

        if (!isset($params['vat'])) {
            // todo: Get default VAT value
            $params['vat'] = 0;
        }

        // Calc contract require pay amount
        $requirePayAmount = isset($params['require_pay_amount']) ? $params['require_pay_amount'] : 0;

        // Get addition attribute
        $additionAttributes = '';

        if (isset($params['addition_attribute'])) {
            if (is_array($params['addition_attribute'])) {
                $additionAttributes = json_encode($params['addition_attribute']);
            } else {
                $additionAttributes = $params['addition_attribute'];
            }
        }

        $commissionSaleValue = 0;
        $commissionSaleType = 0; // 0: percent; 1: fix-amount
        $commissionSaleAmount = 0;
        $commissionProductAmount = 0;
        
        $product_code = "";

        if (!isset($params['commission_sale'])) {
            // Calc commission for sale
            // Get sale type first
            if (isset($params['sale_type'])) {
                switch ($params['sale_type']) {
                    case InsuranceContract::SALE_TYPE_AGENCY:
                        if (!empty($saleTypeId)) {
                            // Get agency commission level
                            $commissionSaleValue = InsuranceAgency::getCommissionAmountByProduct($saleTypeId, $params['product_id']);
                            $commissionSaleAmount = ($params['product_price'] * ((float)$commissionSaleValue / 100));
                        }
                        break;
                }
            }
        } else {
            $commissionSaleAmount = $params['commission_sale'];
        }
       
        // Get point for customer
        $customerCommissionAmount = 0;
        $chkProduct = false;
        // Check insurance type
        if ($insuranceType) {
            switch ($insuranceType->apply_fee_type) {
                case InsuranceType::APPLY_FEE_TYPE_BENEFICIARY:
                    // Get products by beneficiary
                    $customerCommission = [];
                    if (!empty($params['beneficiary'])) {
                        foreach ($params['beneficiary'] as $beneficiary) {
                            if (isset($beneficiary['product_id']) && !empty($beneficiary['product_id'])) {
                                if($chkProduct == false){
                                    $params["product_id"] = $beneficiary['product_id'];
                                    $product = Product::getProduct($params['product_id']);
                                    
                                    if(!empty($product) && !empty($product->code)){
                                        $product_code = $product->code;
                                    }
                                    
                                    $chkProduct = true;
                                }
                                
                                $productCommission = ProductCustomerCommission::getByProduct($beneficiary['product_id']);
                                if ($productCommission) {
                                    $customerCommission[] = [
                                        'product_id' => $beneficiary['product_id'],
                                        'product_price' => (float)$beneficiary['product_price'],
                                        'commission_type' => $productCommission->commission_type,
                                        'commission_amount' => $productCommission->commission_amount
                                    ];
                                }
                                if ($params['sale_type'] == InsuranceContract::SALE_TYPE_AGENCY &&  !empty($saleTypeId)) {
                                    // Get agency commission level
                                    $commissionSaleValue = InsuranceAgency::getCommissionAmountByProduct($saleTypeId, $beneficiary['product_id']);
                                    $commissionSaleAmount += ($beneficiary['product_price'] * ((float)$commissionSaleValue / 100));
                                }
                                
                                $productCommissionInfo = ProductCommission::getCommistionInfo($beneficiary['product_id'], !empty($params["is_check_subsidiaries"]) ? $params["is_check_subsidiaries"] : 0, !empty($params["insurance_subsidiaries_id"]) ? $params["insurance_subsidiaries_id"] : 0);
                                if(!empty($productCommissionInfo)){
                                    if(empty($productCommissionInfo->commission_type)){
                                        //HOA HỒNG : = phí bảo hiểm sau chiết khấu của công ty bảo hiểm * tỉ lệ hoa hồng ( đối với các đơn ko có thuế )
                                        //HOA HỒNG : = Phí bảo hiểm sau chiết khấu của công ty bảo hiểm / 1.1 * tỉ lệ hoa hồng

                                        $vat = (int)str_replace('%','',$params["vat"]);
                                        if(isset($params["discount_amount_of_insurance_company_type"]) && !empty($params["discount_amount_of_insurance_company_type"])) {
                                            $discountAmountOfInsuranceCompany = $params["discount_amount_of_insurance_company"];
                                        } else {
                                            if(isset($params["discount_amount_of_insurance_company"])) {
                                                $discountAmountOfInsuranceCompany = $params["gross_amount"] * $params["discount_amount_of_insurance_company"]/100;
                                            } else {
                                                $discountAmountOfInsuranceCompany = 0;
                                            }
                                        }
                                        $payAmountAfterDiscountAmountOFInsuranceCompany = $params["gross_amount"] - $discountAmountOfInsuranceCompany;
                                        $finalPayAmount = !empty($vat) ? (float)($payAmountAfterDiscountAmountOFInsuranceCompany / 1.1) : $payAmountAfterDiscountAmountOFInsuranceCompany;
                                        $commissionProductAmount += ((float)$productCommissionInfo->commission_amount / 100) * $finalPayAmount;
                                    }else{
                                        $commissionProductAmount += (float)$productCommissionInfo->commission_amount;
                                    }

                                }
                            }
                        }
                    }

                    if (!empty($customerCommission)) {
                        foreach ($customerCommission as $commission) {
                            if ($commission['commission_type'] == 0) {
                                $customerCommissionAmount += ($commission['product_price'] * ((float)$commission['commission_amount'] / 100));
                            } else {
                                $customerCommissionAmount += $commission['commission_amount'];
                            }
                        }
                    }
                    break;
                case InsuranceType::APPLY_FEE_TYPE_CONTRACT:
                    $product = Product::getProduct($params['product_id']);
                    if(!empty($product) && !empty($product->code)){
                        $product_code = $product->code;
                    }
                    $customerCommission = $product->customer_commission();
                    
                    if (!empty($customerCommission)) {
                        if ($customerCommission->commission_type == 0) {
                            $customerCommissionAmount = ($params['product_price'] * ((float)$customerCommission->commission_amount / 100));
                        } else {
                            $customerCommissionAmount = $customerCommission['commission_amount'];
                        }

                        $customerCommission = $customerCommission->toArray();
                    }
    
                    $productCommissionInfo = ProductCommission::getCommistionInfo($params['product_id'], !empty($params["is_check_subsidiaries"]) ? $params["is_check_subsidiaries"] : 0, !empty($params["insurance_subsidiaries_id"]) ? $params["insurance_subsidiaries_id"] : 0);
                    if(!empty($productCommissionInfo)){
                        if(empty($productCommissionInfo->commission_type)){
                            //HOA HỒNG : = phí bảo hiểm sau chiết khấu của công ty bảo hiểm * tỉ lệ hoa hồng ( đối với các đơn ko có thuế )
                            //Hoa HỒNG : = Phí bảo hiểm sau chiết khấu của công ty bảo hiểm / 1.1 * tỉ lệ hoa hồng
                            $vat = (int)str_replace('%','',$params["vat"]);
                            if(isset($params["discount_amount_of_insurance_company_type"]) && !empty($params["discount_amount_of_insurance_company_type"])) {
                                $discountAmountOfInsuranceCompany = $params["discount_amount_of_insurance_company"];
                            } else {
                                if(isset($params["discount_amount_of_insurance_company"])) {
                                    $discountAmountOfInsuranceCompany = $params["gross_amount"] * $params["discount_amount_of_insurance_company"]/100;
                                } else {
                                    $discountAmountOfInsuranceCompany = 0;
                                }
                            }
                            $payAmountAfterDiscountAmountOFInsuranceCompany = $params["gross_amount"] - $discountAmountOfInsuranceCompany;
                            $finalPayAmount = !empty($vat) ? (float)($payAmountAfterDiscountAmountOFInsuranceCompany / 1.1) : $payAmountAfterDiscountAmountOFInsuranceCompany;
                            $commissionProductAmount += ((float)$productCommissionInfo->commission_amount / 100) * $finalPayAmount;

                        }else{
                            $commissionProductAmount += (float)$productCommissionInfo->commission_amount;
                        }
                    }
                    
                break;
            }
        }

        // Get customer info
        $customerInfo = Customer::getDetail($params['customer_id']);
        $customerInfo = json_encode($customerInfo->toArray());

        // Get extra product
        $extraProducts = '';
        if (isset($params['use_fees']['extra_product'])) {
            $extraProducts = is_array($params['use_fees']['extra_product']) ? json_encode($params['use_fees']['extra_product']) : $params['use_fees']['extra_product'];
        }
        // Get extra fee
        $extraFees = '';
        if (isset($params['use_fees']['extra_fee'])) {
            $extraFees = is_array($params['use_fees']['extra_fee']) ? json_encode($params['use_fees']['extra_fee']) : $params['use_fees']['extra_fee'];
        }

        // Get extra fee attribute
        $extraFeeAttributes = '';
        if (isset($params['extra_fee_attributes'])) {
            $extraFeeAttributes = is_array($params['extra_fee_attributes']) ? json_encode($params['extra_fee_attributes']) : $params['extra_fee_attributes'];
        }

        /*
         * Add selected_price_type when create in web to display in mobile, type travel
         */
        if ($params['insurance_type_id'] == 3 && empty($params['selected_price_type'])) {
                //type travel and create in web
                if (!empty($params['price_type']['fee_type'])) {
                    $params['selected_price_type'] = $params['price_type']['fee_type'];
                }
        }
        //add start_time and year_interval_value in filter_data in web to display in mobile, type: car, apartment
        // if (empty($params['selected_price_type'])) {
        //     if ($params['insurance_type_id'] == 2 || $params['insurance_type_id'] == 4) {
        //         $filterData = $params['filter_data'];
        //         $filterData['start_time'] = $params['start_time'];
        //         $filterData['year_interval_value'] = $params['year_interval_value'];
        //         $params['filter_data'] = $filterData;
        //         $extraProducts = !empty($params['use_fees']['extra_product']) ? json_encode($params['use_fees']['extra_product']) : '';
        //         $params['selected_price_type'] = !empty($params['price_type']) ? implode(',', array_keys($params['price_type'])) : '';
        //         $extraFees = !empty($params['use_fees']['extra_fee']) ? json_encode($params['use_fees']['extra_fee']) : '';
        //     }
        // }

        if (empty($params['selected_price_type'])) {
            if ($params['insurance_type_id'] == 2 || $params['insurance_type_id'] == 4) {
                $filterData = $params['filter_data'];
                $filterData['start_time'] = $params['start_time'];
                $filterData['year_interval_value'] = $params['year_interval_value'];
                $params['filter_data'] = $filterData;
                $extraProducts = !empty($params['use_fees']['extra_product']) ? json_encode($params['use_fees']['extra_product']) : '';
                $price_type = json_decode($params['price_type'], true);
                $price_type = array_keys($price_type);
                $params['selected_price_type'] = !empty($params['price_type']) ? implode(',', $price_type) : '';
                $extraFees = !empty($params['use_fees']['extra_fee']) ? json_encode($params['use_fees']['extra_fee']) : '';
            }
        }
        $extraProductFilterData = [];
        //extra_product_filter_data
        if (!empty($params['extra_products_value'])) {
            $extraProductFilterData = json_decode($params['extra_products_value'], true);
            $extraProductFilterData['price_type'] = $params['extra_products_price_type'];
        }
        for ($i=0; $i <= count($params['beneficiary']) ; $i++) { 
            if(!empty($params['beneficiary'][$i]['phone_number'])){
                $check_phone_number_customer = Customer::where('phone_number', $params['beneficiary'][$i]['phone_number'])->exists();
                if ($check_phone_number_customer == false) {
                    do   
                    {
                        $invitation_code = str_random(8);
                        $customer = Customer::where('invitation_code', $invitation_code)->first();
                    }
                    while(!empty($customer));
                    $result = Customer::create([
                        "name" => isset($params['beneficiary'][$i]['name']) ? $params['beneficiary'][$i]['name'] : $params['beneficiary'][$i]['name12'],
                        "customer_manager_id"  =>'',
                        "phone_number" => $params['beneficiary'][$i]['phone_number'],
                        "email" => '',
                        "sex"   => $params['beneficiary'][$i]['sex'], 
                        "date_of_birth" => $params['beneficiary'][$i]['date_of_birth'],
                        "code_customer" => '', 
                        "source" => '', 
                        "classify" => 1, 
                        "password" => Hash::make('monfin'),
                        "identity_card" => '',
                        "address" => '',
                        "type_id" => '',
                        "created_by" => '',
                        "updated_by" => '',
                        "invitation_code" => $invitation_code,
                        "zalo"      => '',
                        "facebook"  => '',
                        "province_id"=> '',
                        "district_id"=> '',
                        "ward_id"=> '',
                        "is_vip" => '',
                        'insurance_demand' => ''
                    ]);
                }
            }
        }
        DB::beginTransaction();
        try {
            $obj = self::create([
                "code"          => isset($params['code']) ? $params['code'] : '',
                "description"   => isset($params['description']) ? $params['description'] : '',
                "note"          => isset($params["note"]) ? $params["note"] : "",
                "customer_id"   => $params["customer_id"],
                "vat"           => $params["vat"],
                "product_id"    => $params["product_id"],
                'product_code'  => isset($params['product_code']) ? $params['product_code'] : $product_code,
                'product_price' => isset($params['product_price']) ? (float)$params['product_price'] : 0,
                'main_fee'      => isset($params['use_fees']['main_fee']) ? (float)$params['use_fees']['main_fee'] : 0,
                'selected_price_type' => isset($params['selected_price_type']) ? $params['selected_price_type'] : '',
                'insurance_quotation_id'  => isset($params['insurance_quotation_id']) ? $params['insurance_quotation_id'] : 0,
                "type_id"       => $params['insurance_type_id'],
                "sale_type"     => $params["sale_type"],
                "sale_type_id"  => $saleTypeId,
                "status"        => self::STATUS_INACTIVE,
                'certificate_active' => -1,
                "start_time"    => $startTime,
                "end_time"      => $endTime,
                "gross_amount"  => isset($params["gross_amount"]) ? $params["gross_amount"] : 0,
                "net_amount"    => isset($params["net_amount"]) ? $params["net_amount"] : 0,
                'discount_amount' => isset($params['discount_amount']) ? $params['discount_amount'] : 0,
                'discount_type' => isset($params['discount_type']) ? $params['discount_type'] : 0,
                'discount_amount_of_insurance_company_type' => isset($params['discount_amount_of_insurance_company_type']) ? $params['discount_amount_of_insurance_company_type'] : 0,
                'discount_amount_of_insurance_company' => isset($params['discount_amount_of_insurance_company']) ? $params['discount_amount_of_insurance_company'] : 0,
                'renewals_customer_status' => isset($params['renewals_customer_status']) ? $params['renewals_customer_status'] : 0,
                'renewals_number_contract' => isset($params['renewals_number_contract']) ? $params['renewals_number_contract'] : 0,
                'require_pay_amount' => $requirePayAmount,
                "paid_amount"   => 0,
                "payment_status" => self::PAYMENT_STATUS_NOT,
                "commission_product" => $commissionProductAmount,//isset($params["commission_product"]) ? $params["commission_product"] : 0,
                "commission_sale" => json_encode([
                    'type'  => $commissionSaleType,
                    'value' => $commissionSaleValue
                ]),
                'commission_sale_amount' => $commissionSaleAmount,
                'commission_customer' => isset($customerCommission) ? json_encode($customerCommission) : '',
                'commission_customer_amount' => $customerCommissionAmount,
                'filter_data'   => isset($params['filter_data']) ? json_encode($params['filter_data']) : '',
                'addition_attributes' => $additionAttributes,
                'extra_products' => $extraProducts,
                'extra_fees' => $extraFees,
                'extra_fee_attributes' => $extraFeeAttributes,
                "created_by"    => $createId,
                "updated_by"    => 0,
                'customer_detail' => $customerInfo,
                'extra_product_filter_data' => !empty($extraProductFilterData) ? json_encode($extraProductFilterData) : null,
                'subsidiaries' => !empty($params["is_check_subsidiaries"]) && (!empty($params["insurance_subsidiaries_id"])) ? $params["insurance_subsidiaries_id"] : 0,
                'created_type' => $params['created_type'],
                'created_id' => $params['created_id'],
            ]);

//             $passwordOrigin = $request['password'];
            $passwordOrigin = 'monfin';
            //     //send mail agency create

            MailQueue::saveMailToQueue([
                'send_to' => json_encode([$result->email]),
                'sender' =>  env('MAIL_FROM_NAME').' <'.env('MAIL_FROM_ADDRESS').'>',
                'subject' => (new AgencyCreate($result, $passwordOrigin))->subjectEmail(),
                'variable' => json_encode([
                    'data' => ['name' => $result->name,'id' => $result->id, 'email' => $result->email],
                    'passwordOrigin' => $passwordOrigin
                ]),
                'templete' => 'insurance::emails.agencyCreate'
            ]);

            // Mail::to($request->email)->send(new AgencyCreate($result, $passwordOrigin));

            // Update contract file
            if (isset($params['file_ids'])) {
                $fileIds = [];
                if (!is_array($params['file_ids'])) {
                    $fileIds = json_decode($params['file_ids']);
                }

                if (empty($fileIds) || !is_array($fileIds)) {
                    $fileIds = explode(',', $params['file_ids']);
                }

                if (!empty($fileIds)) {
                    // Update contract id
                    InsuranceContractFile::whereIn('id', $fileIds)->update(['contract_id' => $obj->id]);
                }
            }
            //Save Payments
            $obj->update_payment(isset($params['payment']) ? $params['payment'] : null);
            // Save Beneficiary Attribute
            $obj->saveBeneficiaryAttributes(isset($params["beneficiary"]) ? $params["beneficiary"] : null);
            // Save Files
            $obj->saveFiles(isset($params["ownerContractFiles"]) ? $params["ownerContractFiles"] : [], InsuranceContractFile::TYPE_OWNER);
            $obj->saveFiles(isset($params["benificaryContractFiles"]) ? $params["benificaryContractFiles"] : [], InsuranceContractFile::TYPE_BENEFICARY);
            $obj->saveFiles(isset($params["certificateContractFiles"]) ? $params["certificateContractFiles"] : [], InsuranceContractFile::TYPE_CERTIFICATE);
            $obj->saveFiles(isset($params["otherContractFiles"]) ? $params["otherContractFiles"] : [], InsuranceContractFile::TYPE_OTHER);

            // Update quotation data
            if (!empty($obj->insurance_quotation_id)) {
                InsuranceQuotation::where('id', $obj->insurance_quotation_id)->update(['insurance_contract_id' => $obj->id]);
            }
            DB::commit();
            // Clear cache
            Cache::tags(['list_contract_agency_' . $saleTypeId, 'list_contract_customer_' . $params["customer_id"], 'list_contract'])->flush();
            if (empty($params["current_customer_id"]))
                return ['success' => true, 'message' => 'Thêm hợp đồng thành công', 'contract' => $obj->toArray()];
            else
                return ['success' => true, "customer_id" => $params["current_customer_id"], 'contract' => $obj->toArray(), 'message' => 'Thêm hợp đồng thành công'];
        } catch (\Exception $e) {
            DB::rollback();
            Log::alert($e);
            return ['success' => false, "message" => "Lỗi không lưu được bản ghi!"];
        }
    }

    /**
     * update payment when for api store contract for agency
     */
    public static function updatePaymentAgency($params, $contractId)
    {
        if ($params != null){
            $params = json_decode($params, true);
            foreach ($params as $key => $val){
                $obj = new InsuranceContractPayment();
                $obj->contract_id = $contractId;
                $obj->amount      = isset($val['amount']) ? $val['amount'] : 0;
                $obj->pay_type    = isset($val['pay_type']) ? $val['pay_type'] : 0;
                $obj->pay_type_detail = isset($val['pay_type_detail']) ? $val['pay_type_detail'] : '';
                $obj->payment_fee  = isset($val['payment_fee']) ? $val['payment_fee'] : 0;
                $obj->payment_time = date('Y-m-d H:i:s');
                $obj->payment_detail = isset($val['payment_detail']) ? $val['payment_detail'] : '';
                $obj->created_by = isset($val['created_by']) ? $val(['created_by']) : 0;
                $obj->status = isset($val['status']) ? 1 : 0;
                $obj->save();
            }
        }
    }

    public function saveBeneficiaryAttributes($params) {
        if (is_array($params) && !empty($params)) {
            $type = $this->insurance_type()->first();
            // Delete
            InsuranceContractBeneficiary::where([
                'contract_id' => $this->id,
            ])->delete();
            // Insert new
            foreach ($params as $val){
                if(isset($val['sex'])){
                    if($val['sex'] == 'M' || $val['sex'] == 'm'){
                        $val['sex'] = 'Nam';
                    }
                    if($val['sex'] == 'F' || $val['sex'] == 'f'){
                        $val['sex'] = 'Nữ';
                    }
                    if($val['sex'] == '0'){
                        $val['sex'] = 'Nam';
                    }
                    if($val['sex'] == '1'){
                        $val['sex'] = 'Nữ';
                    }
                    if($val['sex'] == '2'){
                        $val['sex'] = 'Khác';
                    }
                }
                $selectedPriceTypes = '';
                if (isset($val['price_types'])) {
                    $selectedPriceTypes = is_array($val['price_types']) ? implode(',', $val['price_types']) : '';
                }
                $obj = new InsuranceContractBeneficiary();
                $obj->beneficiary_type_id = $type->beneficiary_type_id;
                $obj->contract_id = $this->id;
                $obj->product_id = isset($val['product_id']) ? $val['product_id'] : 0;
                $obj->product_code = isset($val['product_code']) ? $val['product_code'] : '';
                $obj->product_price = isset($val['product_price']) ? $val['product_price'] : '';
                $obj->gross_amount = isset($val['product_price']) ? $val['product_price'] : '';
                $obj->net_amount = isset($val['product_price']) ? $val['product_price'] : '';
                $obj->selected_price_type = $selectedPriceTypes;
                $obj->price_type_custom_health_insurance = \GuzzleHttp\json_encode(!empty($val["custom_price_type"]) ? $val["custom_price_type"] : []);
                $obj->value = json_encode($val);
                $obj->save();
            }
        }

        // Clear cache
        Cache::tags('contract_beneficiary')->flush();
    }

    public function saveFiles($params, $type) {
        if (is_array($params) && !empty($params)) {
            $keepIds = [];
            $newFiles = [];
            foreach ($params as $param) {
                if (empty($param["id"])) {
                    // insert new file
                    $file = new InsuranceContractFile();
                    $file->type = $type;
                    $file->contract_id = $this->id;
                    $file->file_name = $param["name"];
                    $file = $file->saveFile($param["file"]);
                    array_push($newFiles, $file->toArray());
                } else {
                    // update exist file
                    if (!empty($param['name'])) {
                        array_push($keepIds, intval($param["id"]));
                    }
                    $file = $this->files->where('id', $param["id"])->first();
                    if ($file) {
                        if (isset($param["file"])) {
                            $file = $file->saveFile($param["file"]);
                        }
                        $file->file_name = isset($param["name"]) ? $param["name"] : '';
                        $file->save();
                    }
                }
            }
            // delete files
            $this->files()->where('type', $type)
                ->whereNotIn('id', $keepIds)
                ->delete();
            // Insert buld
            InsuranceContractFile::insert($newFiles);
        }
    }

    /**
     * @param $insuranceTypeId
     */
    public static function getByType($insuranceTypeId)
    {
        return self::where('type_id', $insuranceTypeId)->first();
    }

    public function update_payment($params)
    {
        InsuranceContractPayment::where('contract_id',$this->id)->delete();

        if ($params != null){
            foreach ($params as $val){
                $obj = new InsuranceContractPayment();
                $obj->contract_id = $this->id;
                $obj->amount      = isset($val['amount']) ? $val['amount'] : 0;
                $obj->pay_type    = isset($val['pay_type']) ? $val['pay_type'] : 0;
                $obj->pay_type_detail = isset($val['pay_type_detail']) ? $val['pay_type_detail'] : '';
                $obj->payment_fee  = isset($val['payment_fee']) ? $val['payment_fee'] : 0;
                $obj->payment_time = $val['payment_time'];
                $obj->payment_detail = $val['payment_detail'];
                $obj->created_by = $val['created_by'];
                $obj->status = isset($val['status']) ? 1 : 0;
                $obj->save();
            }
        }
    }

    /**
     * Add payment for insurance contract
     *
     * @param $amount
     * @return bool
     */
    public function addPayment($amount)
    {
        if ($amount <= 0) {
            return false;
        }

        $this->paid_amount += (float)$amount;

        // Check for payment status
        if ($this->paid_amount >= $this->require_pay_amount) {
            $this->payment_status = self::PAYMENT_STATUS_COMPLETED;
        } else {
            $this->payment_status = self::PAYMENT_STATUS_IN_PROGRESS;
        }

        return $this->save();
    }

    public function getRequirePayAmount()
    {
        return $this->require_pay_amount;
    }

    /**
     * @param $id
     * @return Model|null|static
     */
    public static function getDetail($id)
    {
        return self::with(['product', 'product.company', 'insurance_type', 'customer','beneficiary', 'agences'])->where('id', $id)->first();
    }

    /**
     * @param $filterData
     * @param int $page
     * @param int $pageSize
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList($filterData, $page = 1, $pageSize = 15)
    {
        $query = self::with(['product', 'insurance_type', 'customer']);

        if (isset($filterData['customer_ids'])) {
            $query->whereIn('customer_id', $filterData['customer_ids']);
        }

        if (isset($filterData['product_ids'])) {
            $query->whereIn('product_id', $filterData['product_ids']);
        }

        return $query->paginate($pageSize, ['*'], 'page', $page);
    }

    /**
     * @param $contractId
     * @return mixed|string
     */
    public static function getSellerEmail($contractId)
    {
        // Get contract info
        $contract = self::getDetail($contractId);
        $email = '';
        if (isset($contract->sale_type) && !empty($contract->sale_type)) {
            switch ($contract->sale_type) {
                case 1:
                    // Agency
                    $agency = InsuranceAgency::getDetail($contract->sale_type_id);
                    $email = isset($agency->email) ? $agency->email : '';
                    break;
                case 2:
                    // User
                    $user = User::getDetail($contract->sale_type_id);
                    $email = isset($user->email) ? $user->email : '';
                    break;
            }
        }

        return $email;
    }

    /**
     * Get seller object
     * @param $contractId
     * @return mixed|string
     */
    public static function getSeller($contractId)
    {
        // Get contract info
        $contract = self::getDetail($contractId);
        if (isset($contract->sale_type) && !empty($contract->sale_type)) {
            switch ($contract->sale_type) {
                case 1:
                    // Agency
                    return InsuranceAgency::getDetail($contract->sale_type_id);
                    break;
                case 2:
                    // User
                    return User::getDetail($contract->sale_type_id);
                    break;
            }
        }

        return false;
    }

    // Cung cấp chứng chỉ api
    public static function getCertificate($contractId){
        // Kết nối API chứng chỉ
        if(isset($contractId)){
            $params['code'] = $contractId;
            $data_request = InsuranceContract::with('insurance_type')->with('product')->with('customer')->where("id", $contractId)->first();
            $data_benefit = InsuranceContractBeneficiary::where("contract_id", $contractId)->get();
            foreach($data_benefit as $item){
                $ben_field = json_decode($item['value'], true);
                $date = str_replace('/', '-', $ben_field['date_of_birth']);
                $time = strtotime($date);
                $newFormat = date('dmY', $time);

                if(isset($ben_field['name'])) {
                    $FullName = $ben_field['name'];
                } else if(isset($ben_field['name12'])) {
                    $FullName = $ben_field['name12'];
                } else {
                    $FullName = "";  
                }

                $Sex = (isset($ben_field['sex'])) ? (($ben_field['sex'] == 'Nam') ? 0 : 1) : "";
                $BirthDay = (isset($ben_field['date_of_birth']) ? $newFormat : '');
                if(isset($ben_field['CMT1'])) {
                    $Passport = $ben_field['CMT1'];
                } else if( isset($ben_field['identity_card'])) {
                    $Passport = $ben_field['identity_card'];
                } else {
                    $Passport = "";
                }
                $Email = (isset($ben_field['email']) ? $ben_field['email'] : '');
                $Phone = (isset($ben_field['phone_number']) ? $ben_field['phone_number'] : '');
                $arr_ben[] = array(
                    "FullName" => $FullName,
                    "Sex" => $Sex,
                    "BirthDay" => $BirthDay,
                    "Passport" => $Passport,
                    "Email" => $Email,
                    "Phone" => $Phone
                );            
            }

            $FeeValue = (string) $data_request['net_amount'];
            $ValueMoney = (string) $data_request['product']['Insurance_money'];
            $sign = md5($ValueMoney.env("KEYCP") . $data_request['customer']['email'] . $data_request['customer']['phone_number'] . $data_request['customer']['identity_card'] . $contractId . $FeeValue);
            $data = [
                "StartTime" => date(('dmY'),strtotime($data_request['start_time'])),
                "ValueMoney" => $data_request['product']['Insurance_money'],
                "FullName"=> $data_request['customer']['name'],
                "Email"=> $data_request['customer']['email'],
                "ProvinceId"=>$data_request['customer']['province_id'],
                "Phone"=>$data_request['customer']['phone_number'],
                "Address"=>$data_request['customer']['address'],
                "Sex"=>$data_request['customer']['sex'],
                "CPId"=>env("CPID"),
                "BirthDay"=>$data_request['customer']['date_of_birth'],
                "Sign"=>$sign,
                "Passport"=> $data_request['customer']['identity_card'],
                "ListAttachPeople"=>$arr_ben,
                "EndTime"=>date(('dmY'),strtotime($data_request['end_time'])),
                "Program"=>($data_request['type_id'] == 22) ? "HS" : "PA",
                "Note"=>$data_request['customer']['note'],
                "RequestId"=>$contractId,
                "FeeValue"=>$data_request['net_amount']
            ];
            $data_string = json_encode($data);
            $curl = curl_init(env('VPI_URL'));

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
            );

            $result = curl_exec($curl);
            $data_array = json_decode($result, true);
            curl_close($curl);
            return $data_array;
        }
    }

    /**
     * @param $contractId
     * @param string $contractCode
     * @param bool $paymentStatus
     * @return array
     */
    public static function provideContract($contractId, $contractCode = '', $paymentStatus = false)
    {
        // Get contract info
        $contract = self::getDetail($contractId);

        // Get list beneficiaries
        $listBeneficiaries = InsuranceContractBeneficiary::getListByContract($contractId);
        // Check time
        $time_start = Carbon::parse($contract->start_time);
        $time_end   = Carbon::parse($contract->end_time);
        if ($time_start->gt($time_end)) {
            return ['success' => false, 'message' => 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu'];
        }

        if (!empty($listBeneficiaries)) {
            // Check can provide online or not
            $contractService = new InsuranceContractService();
            $service = $contractService->hasOnlineProvide($contract->product_code);

            if (!$service) {
                $contract->certificate_active = 0 ;
                // Check insurance contract code
                if (empty($contractCode) &&  $contract->type_id != 22 && $contract->type_id != 21 && $contract->type_id != 23) {
                    return ['success' => false, 'message' => 'Vui lòng nhập số hợp đồng'];
                } else {
                    try {
                        // Cap don tay
                        $check_code = self::where('code', $contractCode)->where('id', '<>', $contract->id)->exists();
                        if ($check_code) {
                            return ['success' => false, 'message' => 'Số hợp đồng đã tồn tại, vui lòng thử lại'];
                        }

                        // call API certificate
                        //tai nạn và nằm viện
                        if($contract->type_id == 22 || $contract->type_id == 21){
                            $certificateAPI = InsuranceContract::getCertificate($contractId);
                        }
                        //bảo hiểm máy bay
                        if($contract->type_id == 23){
                            $flight = Flight::order($contractId);
                            if($flight['status'] == 500) {
                                return ['success' => false, 'message' => $flight['message']];
                            }
                        }

                        $exportContractHelper = new ExportContractHelper();
                        // dd($exportContractHelper->export($contract, $listBeneficiaries, $contractCode));
                        if ($exportContractHelper->export($contract, $listBeneficiaries, $contractCode)) {
                            // Clear cache
                            Cache::forget('list_contract_files_' . $contract->id);

                            dispatch(new NotifyContractProvideSuccess($contractId));
                        } else {
                            return ['success' => false, 'message' => 'Xảy ra lỗi khi tạo giấy chứng nhận, vui lòng thử lại sau.'];
                        }
                    }catch(\Exception $ex){
                        Log::error($ex->getMessage() . '. ' . $ex->getFile() . ':' . $ex->getLine());
                        return ['success' => false, 'message' => $ex->getMessage()];
                    }
                }
            } else {
                // Provide contract online
                try {
                    // Provide contract with service
                    $contractService->setService($service);
                    $result = $contractService->provideContract($contract, $paymentStatus);

                    if (isset($result['error'])) {
                        return ['success' => false, 'message' => $result['error']];
                    } else {
                        $contract->code = $result['id_hd'];
                        $contract->contract_number = $result['so_hd'];
                        $contract->provide_service = $contractService->getName();
                        $contract->certificate_active = 0;
                        // Check payment status
                        if ($paymentStatus) {
                            $contract->status = 1;
                            $contract->certificate_active = 1;
                        }

                        $contract->save();
                        // Update code for beneficiary
                        // check ket qua tu vnpay tra ve, neu id_id va beneficiary_code null
                        // call api update lai ket qua
                        if (isset($result['beneficiary_code'])) {
                            foreach ($result['beneficiary_code'] as $id => $val) {
                                InsuranceContractBeneficiary::where('id', $id)
                                    ->update([
                                        'contract_code'    => isset($result['id_hd']) ? $result['id_hd'] : '',
                                        'beneficiary_code' => implode($val, ',')
                                    ]);
                            }

                        }
                        //check neu VBI tra ve beneficiary_code = null
                        if(empty($result['beneficiary_code']) || empty($result['id_hd'])){
                            $so_id_dtac['so_id_dtac'] = $contractId;
                            $url = 'https://api.evbi.vn/api/ebh/get_id';
                            $method = 'GET';
                            $type = 'json';
                            $result_api_return = GetDataFromAPI::getDataFromApi($so_id_dtac, $url,$method);
                            // tra ve du lieu sau kh goi api get_id
                            $result_api_return = json_decode($result_api_return, true);
                            foreach ($result_api_return['data'] as $data) {
                                foreach ($listBeneficiaries as $beneficiary) {
                                    $beneficiary_value = json_decode($beneficiary['value'], true);
                                    $beneficiary_name = $beneficiary_value['name'];
                                    $percent = 0;
                                    // so sanh ten cua nguoi thu huong ma ten_ndbh cua api tra ve
                                    similar_text($beneficiary_name, $data["ten_ndbh"], $percent );
                                    if ($percent == 100){
                                        $beneficiary['contract_code'] = isset($data['so_id_vbi']) ? $data['so_id_vbi'] : '';
                                        $beneficiary['beneficiary_code'] = isset($data['so_id_dt_vbi']) ? $data['so_id_dt_vbi'] : '';
                                    }
                                }
                            }
                        }

                        if(isset($certificateAPI)){
                            return ['status' => $certificateAPI['Status'], 'message' => $certificateAPI['Message']];
                        }
                    }
                } catch (\Exception $ex) {
                    Log::error($ex->getMessage() . '. File ' . $ex->getFile() . '. Line ' . $ex->getLine());

                    return ['success' => false, 'message' => $ex->getMessage()];
                }
            }
            
            // Clear cache
            Cache::tags(['contract_info_' . $contract->id])->flush();

            return ['success' => true, 'message' => 'Cấp đơn thành công'];
        } else {
            return ['success' => false, 'message' => 'Hợp đồng chưa có người hưởng bảo hiểm, chưa thể cấp đơn.'];
        }
    }

    /**
     * @param $contractId
     * @return array
     */
    public static function activeCertificate($contractId)
    {
        $contract = InsuranceContract::getDetail($contractId);
        // Check can provide online or not
        $contractService = new InsuranceContractService();
        $service = $contractService->hasOnlineProvide($contract->product_code);

        if ($service) {
            $result = $service->activeCertificate($contract);

            if (!isset($result['error'])) {
                return ['success' => true, 'message' => 'Xác nhận ký số hợp đồng thành công!', 'contract_number' => isset($result['so_hd']) ? $result['so_hd'] : ''];
            } else {
                return ['success' => false, 'message' => $result['error']];
            }
        } else {
            return ['success' => false, 'message' => 'Không thể tìm thấy dịch vụ ký số.'];
        }
    }

    public static function createContractCode()
    {
        return number_format(microtime(true) * 10000, 0, '', '');
    }

    /**
     * Count number benefice per list of contract
     */
    public static function addBeneficeCountToContract($contracts)
    {
        $arrayContractId = [];
        foreach ($contracts as $row) {
            $arrayContractId[] = $row->id;
        }
        $benefice = InsuranceContractBeneficiary::select('id', 'contract_id')
            ->whereIn('contract_id', $arrayContractId)
            ->get();
        $arrayBeneficePerContractId = [];
        foreach ($benefice as $row) {
            $arrayBeneficePerContractId[$row->contract_id][] = $row->id;
        }
        $contractCountBenefice = [];
        foreach ($arrayBeneficePerContractId as $key => $value) {
            $contractCountBenefice[$key] = count($value);
        }
        foreach ($contracts as $row) {
            if (array_key_exists($row->id, $contractCountBenefice)) {
                $row->benefice_count = $contractCountBenefice[$row->id];
            } else {
                $row->benefice_count = 0;
            }
        }
        return $contracts;
    }
    
    /**
     * Check agency access contract
     * @param $contract
     * @param $agency_id
     * @return bool
     */
    public static function checkAgencyAccessContract($contract, $agency_id){
        if(empty($contract) || empty($agency_id)) {
            return false;
        }
        if($contract->sale_type_id == $agency_id){
            return true;
        }else{
            $agencyInfo = InsuranceAgency::where("id", $agency_id)->first();
            if(!empty($agencyInfo) && $agencyInfo->agency_company_is_manager == 1){
                $aInfo = InsuranceAgency::where("id", $contract->sale_type_id)->where('agency_company_id', $agencyInfo->agency_company_id)->first();
                if(!empty($aInfo)){
                    return true;
                }else{
                    return false;
                }
            }
            
            return false;
        }
    }
    
    /**
     * Report week
     * @return array
     */
    public function reportMonth($agencyIdArray = []){
        $arrPast = array();
        $arrPast[] = date('Y-m-01');
        for ($i = 1; $i <= 11; $i++) {
            $arrPast[]  = date("Y-m-01", strtotime( date( 'Y-m-01' )." -$i months"));
        }
    
        $result = array();
        for ($i = count($arrPast) - 1; $i >=0 ; $i--){
            $result[$arrPast[$i]] = $this->countTransByDate($arrPast[$i], $agencyIdArray);
        }
        return $result;
    }
    
    /**
     * Count tran by date
     */
    public function countTransByDate($date, $agencyIdArray){
        $query = $this->query()->whereBetween('created_at', array(
            "{$date} 00:00:00", date("Y-m-t", strtotime($date)) . " 23:59:59"
        ));
        if (!empty($agencyIdArray)) {
            $query->whereIn('sale_type_id', $agencyIdArray);
        }
//        return $query->sum('insurance_contracts.paid_amount');
        return (new DashboardController())->calculateCommissionNetAmount($query);
    }

    /**
     * query for report by source
     */
    public static function queryReportBySource($source, $start, $end, $agencyIdArray)
    {
        $query = self::whereRaw('customer_id IN (SELECT id FROM customers WHERE source = '.$source.')');
        if (!empty($agencyIdArray)) {
            $query->whereIn('sale_type_id', $agencyIdArray);
        }
        $data = $query->where('status', '>', self::STATUS_DELETED)->whereBetween('created_at', [$start, $end])->sum('require_pay_amount');
        return $data;
    }
    
    /**
     * Report by source
     */
    public static function reportBySource($start, $end, $agencyIdArray = [])
    {
        $array = [
            'Facebook' => 0,
            'Email marketing' => 1,
            'KH giới thiệu' => 2,
            'Kênh bán' => 3
        ];
        $rs = [];
        foreach ($array as $key => $value) {
            $rs[] = [
                'label' => $key,
                'data' => self::queryReportBySource($value, $start, $end, $agencyIdArray)
            ];
        }
        return $rs;
    }

    /**
     * Confirm commission paid
     */
    public static function confirmCommissionPaid($user, $params)
    {
        $timeNow = date('Y-m-d H:i:s');
        $loginUserId = $user->id;
        $arrayContractId = $params['idArray'];
        DB::beginTransaction();
        try {
            foreach ($arrayContractId as $row) {
                $dataUpdate = [
                    'commission_pay' => self::COMMISSION_PAID,
                    'commision_pay_date' => $timeNow,
                    'commistion_pay_created_id' => $loginUserId,
                    'payment_status' => self::PAYMENT_STATUS_COMPLETED_CTBH
                ];
                self::where('id', $row)->update($dataUpdate);
            }
            DB::commit();
            return 1;
        } catch (\Exception $e) {
            DB::rollback();
            return 0;
        }

    }
    
    /**
     * Get report
     *
     * @param array $params
     * @return $this|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|static
     */
    public static function getReport($params = array()){
        $query = self::where('status','>',-1)->orderBy('created_at','DESC');
        if (!empty($params['start_date']) ) {
            //$contract_all->where('start_time','>=',$startDate);
            $startDate = Carbon::createFromFormat('d/m/Y',$params['start_date'])->toDateString();
            if (empty($params['end_date'])) {
                $endDate = Carbon::now()->toDateString();
            } else {
                $endDate = Carbon::createFromFormat('d/m/Y',$params['end_date'])->toDateString();
            }
            //$contract_all->whereBetween('created_at',[$startDate, $endDate]);
            $query = $query->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        } else {
            if (!empty($endDate)) {
                $endDate = Carbon::createFromFormat('d/m/Y',$endDate)->toDateString();
                $query = $query->whereDate('created_at','<=', $endDate);
            }
        }
    
        if (!empty($params["customer_id"])) {
            $query = $query->where('customer_id',$params["customer_id"]);
        }
    
        if (!empty($params["company_id"])) {
            $companyId = $params["company_id"];
            $query = $query->whereHas('product',function ($query) use ($companyId) {
                $query->where('company_id',$companyId);
            });
        }

        if (!empty($params["agency_id"])) {
            $query = $query->where('sale_type_id', $params["agency_id"]);
        }

        if (!empty($params["type_id"])) {
            $query = $query->where('type_id', $params["type_id"]);
        }
    
        if (!empty($params["insurance_type_id"])) {
            $query = $query->where('type_id', $params["insurance_type_id"]);
        }
    
        if(!empty($userId) || !empty($params["user_id"])){
            $userId = !empty($params["user_id"]) ? $params["user_id"] : $userId;
            $query = $query->whereRaw('(sale_type = 1 AND sale_type_id = ?) OR (sale_type = 2 AND sale_type_id IN (SELECT id FROM insurance_agencies WHERE manager_id = ?))', array($userId, $userId));
        }
    
        if (!empty($params['key_search'])){
            $keySearch = $params['key_search'];
            $query = $query->whereHas('product', function($query)  use ($keySearch) {
                $query->where('name', 'like', '%'.$keySearch.'%');
            });
        }
    
        $query = $query->with('product:id,name,company_id');
        $query = $query->with('customer:id,name');
        $query = $query->with('subsidiary:id,name');
        
        return $query;
    }
    
    /**
     * Get payment info
     *
     * @param $payments
     * @param $pay_type
     * @return array
     */
    public static function getPaymentInfo($payments, $pay_types = array()){
        $result = array(
            'amount'=>0,
            'pinfo'=>array()
        );
        if(!empty($payments)){
            foreach ($payments as $item){
                if(in_array($item->pay_type, $pay_types)){
                    if($item->status == InsuranceContractPayment::STATUS_CONFIRM){
                        $result['amount'] += $item->amount;
                    }
                    $result['pinfo'] = $item;
                }
            }
        }
        return $result;
    }

    public static function encryptRSA($string){
        $rsa = new RSA();
        $rsa->loadKey(env('BL_RSA_PUBLICKEY')); //public key
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        $encrypt = base64_encode($rsa->encrypt($string));
        return $encrypt;
    }

    public static function decryptRSA($string){
        $rsa = new RSA();
        $rsa->loadKey(env('BL_RSA_PRIVATEKEY')); // private key
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        $decrypt = $rsa->decrypt(base64_decode($string));
        return $decrypt;
    }
}
