<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Mail\MailDebugRevenue;
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
use App\Models\GetDataFromAPI;
use App\Models\InsuranceAgency;
use App\Models\Revenue;
use App\Models\RevenueContract;
use App\Models\RevenueDayly;
use App\Models\RevenueMonthly;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductCommission;
// use Modules\Product\Models\InsuranceType;
use Modules\Product\Models\ProductCustomerCommission;
use App\Models\GetAPI;
use App\Models\Flight;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Static_;
use phpseclib\Crypt\RSA;
use App\Models\MailQueue;

class InsuranceContract extends Model
{
    protected $fillable = [
        'insurance_quotation_id',
        'code',
        'url_pti',
        'contract_number',
        'customer_id',
        'insurance_agency_id',
        'insurance_company_id',
        'filter_data',
        'product_id',
        'product_code',
        'main_fee',
        'selected_price_type',
        'extra_products',
        'extra_product_filter_data',
        'extra_fee_attributes',
        'product_price',
        'type_id',
        'addition_attributes',
        'description',
        'note',
        'gross_amount',
        'vat','net_amount',
        'discount_amount',
        'discount_amount_of_insurance_company',
        'discount_amount_of_insurance_company_type',
        'discount_type',
        'require_pay_amount',
        'paid_amount',
        'start_time',
        'end_time',
        'sale_type',
        'sale_type_id',
        'commission_product',
        'commission_sale',
        'commission_sale_amount',
        'commission_customer',
        'commission_customer_amount',
        'status',
        'renewals_customer_status',
        'renewals_number_contract',
        'payment_status',
        'notify_provide_contract',
        'customer_detail',
        'renewal_number',
        'provide_service',
        'updated_by',
        'created_by',
        'certificate_active',
        'get_file_times',
        'commission_pay',
        'commision_pay_date',
        'commistion_pay_created_id',
        'subsidiaries',
        'url_cerficate',
        'url_cerficate_zip',
        'cerficate',
        'created_id',
        'created_type',
        'coupon_code',
        'date_signed',
        'signed_by',
        'revenue_daily_update',
        'revenue_contract_update',
        'update_data_pti',
        'update_revenue_plush',
        'apposition',
        'gcn_upload',
        'revene_chosse',
        'date_contract_online',
        'revenue_daily_update_old',
        'renewals_number_year',
        'pass_ack_date',
        'fee_payment_next_date',
        'fee_payment_date',
        'consulting_information_id',
        'contract_fwd_status',
        'periodic_fee_type',
        'release_date',
        'effective_date',
        'ack_date',
        'change_date',
        'ape_gross',
        'fyp_gross',
        'ape',
        'fyp',
        'ape_net',
        'fyp_net',
        'check_fyp_net',
        'revenue_cycle',
        'salary_payment_status',
        'contract_submission_date',
        'p_fyp',
        'payment_via',





        // 'extra_fees',
        // 'reason_cancel_contract',


        
        // 'selected_condition_price_type',
        // 'so_tc_pti',
    ];

    protected $guarded = [];

    const COMMISSION_NOT_PAID = 0;
    const COMMISSION_PAID = 1;
    /**
     * Relationship
     */

    public function get_status()
    {
        return $this->belongsTo('App\Models\InsuranceContractStatus','status','code');
    }

    public function product_detail()
    {
        return $this->hasMany('App\Models\InsuranceContractDetail','insurance_contract_id');
    }

    public function insurance_agency()
    {
        return $this->belongsTo('App\Models\InsuranceAgency','insurance_agency_id');
    }

    public function consulting_information()
    {
        return $this->belongsTo('App\Models\ConsultingInformation','consulting_information_id');
    }

    public function insurance_type()
    {
        return $this->belongsTo('App\Models\InsuranceType', 'type_id');
    }
    public function product()
    {
        return $this->belongsTo('App\Models\Product');
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
    const NEW_CONTRACT = 0;
    const RENEW_CONTRACT = 1;

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
        
        if(isset($params["sale_type"]) && $params["sale_type"] == self::SALE_TYPE_AGENCY)
            $saleTypeId = isset($params["sale_type_agency_id"]) ? $params["sale_type_agency_id"] : null;
        elseif(isset($params["sale_type"]) && $params["sale_type"] == self::SALE_TYPE_USER)
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
        if(isset($params['relation'])){
            $customerInfo['relation'] = $params['relation'];
        }
        if(isset($params['hight_rick_job'])){
            $customerInfo['hight_rick_job'] = $params['hight_rick_job'];
        }
        if(isset($params['filter_data']['BHTN_time'])){
            $customerInfo['year_interval_value'] = $params['filter_data']['BHTN_time'];
        }
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
                "sale_type"     => isset($params["sale_type"]) ? $params["sale_type"] : 0,
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

            $passwordOrigin = 'monfin';
            //     //send mail agency create
//            MailQueue::saveMailToQueue([
//                'send_to' => json_encode([$request->email]),
//                'sender' =>  env('MAIL_FROM_NAME').' <'.env('MAIL_FROM_ADDRESS').'>',
//                'subject' => (new AgencyCreate($result, $passwordOrigin))->subjectEmail(),
//                'variable' => json_encode([
//                    'data' => ['name' => $result->name,'id' => $result->id, 'email' => $result->email],
//                    'passwordOrigin' => $passwordOrigin
//                ]),
//                'templete' => 'insurance::emails.agencyCreate'
//            ]);

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
    public  static function convert_number_to_words($number) {

        $hyphen      = ' ';
        $conjunction = '  ';
        $separator   = ' ';
        $negative    = 'âm ';
        $decimal     = ' phẩy ';
        $dictionary  = array(
            0                   => 'Không',
            1                   => 'Một',
            2                   => 'Hai',
            3                   => 'Ba',
            4                   => 'Bốn',
            5                   => 'Năm',
            6                   => 'Sáu',
            7                   => 'Bảy',
            8                   => 'Tám',
            9                   => 'Chín',
            10                  => 'Mười',
            11                  => 'Mười một',
            12                  => 'Mười hai',
            13                  => 'Mười ba',
            14                  => 'Mười bốn',
            15                  => 'Mười năm',
            16                  => 'Mười sáu',
            17                  => 'Mười bảy',
            18                  => 'Mười tám',
            19                  => 'Mười chín',
            20                  => 'Hai mươi',
            30                  => 'Ba mươi',
            40                  => 'Bốn mươi',
            50                  => 'Năm mươi',
            60                  => 'Sáu mươi',
            70                  => 'Bảy mươi',
            80                  => 'Tám mươi',
            90                  => 'Chín mươi',
            100                 => 'trăm',
            1000                => 'ngàn',
            1000000             => 'triệu',
            1000000000          => 'tỷ',
            1000000000000       => 'nghìn tỷ',
            1000000000000000    => 'ngàn triệu triệu',
            1000000000000000000 => 'tỷ tỷ'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
// overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . convert_number_to_words(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true)
        {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens = ((int)($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if( $units )
                {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if( $remainder )
                {
                    if($remainder >= 10){
                        $string .= $conjunction . (InsuranceContract::convert_number_to_words( $remainder ));
                    }else{
                        $string .= $conjunction . ' lẻ ' .(InsuranceContract::convert_number_to_words( $remainder ));
                    }

                }
                break;
            default:
                $baseUnit = pow( 1000, floor( log( $number, 1000 ) ) );
                $numBaseUnits = (int)($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = InsuranceContract::convert_number_to_words( $numBaseUnits ) . ' ' . $dictionary[$baseUnit];
                if( $remainder )
                {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    if($remainder >= 10){
                        $string .= InsuranceContract::convert_number_to_words( $remainder );
                    }else{
                        $string .= ' lẻ ' . InsuranceContract::convert_number_to_words( $remainder );
                    }
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
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
                // $obj->beneficiary_type_id = $type->beneficiary_type_id;
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
    public static  function _renturn_string($length,$number)
    {
        for ($i = 1; $i <= $length; $i++) {
            $number = '0'.$number;
        }
        return $number;
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
//        $product = $contract->product;
//        $company =$product->company;
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
                if (empty($contractCode) &&  $contract->type_id != 22 && $contract->type_id != 21 && $contract->type_id != 23  && $contract->type_id != 27) {
                    return ['success' => false, 'message' => 'Vui lòng nhập số hợp đồng'];
                } else {
                    try {
                        // Cap don tay
                        $check_code = self::where('contract_number', $contractCode)->where('id', '<>', $contract->id)->exists();
                        if ($check_code) {
                            return ['success' => false, 'message' => 'Số hợp đồng đã tồn tại, vui lòng thử lại'];
                        }
                        //check san pham offline (thay doi trang thai da cap don)
                        if ($contract->product->product_type_online != 1){
                            $contract->certificate_active = 0;
                            $contract->contract_number = $contractCode;
                            $contract->save();
                            return ['success' => true, 'message' => 'Cấp đơn offline thành công!'];
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
                        // if($company->id == 72){
                        //     $contract->status = 1;
                        //     $contract->certificate_active = 1;
                        // }
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
                        if(isset($result['beneficiary_code']) &&  empty($result['beneficiary_code']) || empty($result['id_hd'])){
                            $so_id_dtac['so_id_dtac'] = $contractId;
                            $url = 'https://api.evbi.vn/api/ebh/get_id';
                            $method = 'GET';
                            $type = 'json';
                            $result_api_return = GetDataFromAPI::getDataFromApi($so_id_dtac, $url,$method);
                            // tra ve du lieu sau kh goi api get_id
                            $result_api_return = json_decode($result_api_return, true);
                            if(!empty($result_api_return['data'])) {
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
                        }

                        if(isset($certificateAPI)){
                            return ['status' => $certificateAPI['Status'], 'message' => $certificateAPI['Message']];
                        }
                        $result['success'] = 'true';
                        $result['message'] = 'Cấp đơn thành công.';
                        return $result;
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
        // $service = $contractService->hasOnlineProvide($contract->product_code);
        $service = $contractService->hasOnlineProvide($contract->product->code);
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

    /**
     * Get payment info if payment = require_pay_amount
     *
     * @param $payments
     * @param $pay_type
     * @return array
     */
    public static function getPaymentInfoWithOutConfirm($payments, $pay_types = array()){
        $result = array(
            'amount'=>0,
            'pinfo'=>array()
        );
        if(!empty($payments)){
            foreach ($payments as $item){
                if(in_array($item->pay_type, $pay_types)){
                    $result['amount'] += $item->amount;
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
    public  static  function checkIsLife($product_id){
        $data_check = Product::where('id',$product_id)->first();
        if($data_check->is_life == 1){
            return true;
        }else{
            return false;
        }

    }

    public static function plusSalaryAgency($agency_id,$contract_id,$self_income){
//        $self_income = 0 ;
//        $check_agency_wallet = AgencyWallet::where('id_agencies', $agency_id)->first();
//        $check_agency_wallet_exchange =  AgencyWalletExchange::where('id_agencies',$agency_id)->first();
//        if($check_agency_wallet == null ){
//            AgencyWallet::create([
//                'id_agencies' => $agency_id,
//                'value' => $self_income,
//            ]);
//            if($check_agency_wallet_exchange){
//                AgencyWalletExchange::create([
//                    'id_agencies' => $agency_id,
//                    'value' => $self_income,
//                    'status' =>1,
//                    'note' => 'Nhận lương hệ thống từ hợp đồng '.$contract_id.' số tiền '.$self_income,
//                    'balance_after_payment' => $check_agency_wallet_exchange->balance_after_payment + $self_income
//                ]);
//            }else{
//                AgencyWalletExchange::create([
//                    'id_agencies' => $agency_id,
//                    'value' => $self_income,
//                    'status' => 1,
//                    'note' => 'Nhận lương hệ thống từ hợp đồng '.$contract_id.' số tiền '.$self_income,
//                    'balance_after_payment' => $self_income
//                ]);
//            }
//            Log::info('[Log_revenue] Tao vi Nap tien vi ten vao vi hop dong : vi so :'.$agency_id.'contractcontroller s 912' );
//        }else{
//
//            $check_agency_wallet->update([
//                'value' => $check_agency_wallet->value + $self_income,
//            ]);
//            if($check_agency_wallet_exchange){
//                AgencyWalletExchange::create([
//                    'id_agencies' => $agency_id,
//                    'value' => $self_income,
//                    'status' => 1,
//                    'note' => 'Nhận lương hệ thống từ hợp đồng '.$contract_id.' số tiền '.$self_income,
//                    'balance_after_payment' => $check_agency_wallet_exchange->balance_after_payment + $self_income
//                ]);
//            }else{
//                AgencyWalletExchange::create([
//                    'id_agencies' => $agency_id,
//                    'value' => $self_income,
//                    'status' => 1,
//                    'note' => 'Nhận lương hệ thống từ hợp đồng '.$contract_id.' số tiền '.$self_income,
//                    'balance_after_payment' => $self_income
//                ]);
//            }
//            Log::info('[Log_revenue]  Nap tien vi ten vao vi hop dong :vi so :'.$agency_id.'contractcontroller s 917' );
//        }
    }

    /**
     * @param $agency_id
     * @param $contract_id
     * @param $self_commission
     * @param int $maxmoney
     * @return array
     */
    public static function incomeTax($agency_id , $contract_id,$self_commission,$maxmoney = 50000000){
        $month=  date('m');
        $year= date('Y');
        $data_contract = InsuranceContract::where('id',$contract_id)->first();
        $check_product = f::where('id',$data_contract->product_id)->first();
        $check_agency =  InsuranceAgency::where('id' , $agency_id)->first();
        $data_reve  =RevenueMonthly::where([['insurance_agency_id',$agency_id],['month','=', $month],['year','=',$year]])->first();
        if($check_product->is_life ==1){
            if($check_agency->life_code  != null ){
                $data= [
                    'life_before_tax'=> round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0),
                    'life_affter_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0),
                    'self_income'=>round($data_contract->require_pay_amount*$check_product->PEYP*0.95*($self_commission/100),0),
                    'self_revenue'=>($check_product->is_agency == 1)?$data_contract->require_pay_amount:0,
                    'nolife_before_tax' => 0,
                    'nolife_affter_tax' => 0,
                ];
            }else{
                $data = [
                    'life_before_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0),
                    'life_affter_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100 ,0),
                    'self_income'=>round( $data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100 , 0) ,
                    'self_revenue'=> intval(($check_product->is_agency == 1)?$data_contract->require_pay_amount : 0),
                    'nolife_before_tax' => 0,
                    'nolife_affter_tax' => 0,
                ];
            }
        }else{
            if($data_reve){
                $data_reve->nolife_before_tax = round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0);
                if(intval($data_reve->nolife_affter_tax)+ intval($data_reve->branch_income) <=$maxmoney){
                    $nolife_affter_tax = round($data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0);
                }else{
                    $nolife_affter_tax = round(  $data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100,0);
                }
                $data=[
                    'nolife_before_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0),
                    'self_revenue'=>($check_product->is_agency == 1)?$data_contract->require_pay_amount : 0,
                    'nolife_affter_tax'=>$nolife_affter_tax,
                    'self_income'=>$nolife_affter_tax,
                    'life_before_tax' => 0,
                    'life_affter_tax' => 0,
                ];
            }else{
                $data=[
                    'nolife_before_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0),
                    'self_revenue'=>($check_product->is_agency == 1)?$data_contract->require_pay_amount:0,
                    'nolife_affter_tax'=>round($data_contract->require_pay_amoun*$check_product->PEYPt*0.95*$self_commission/100,0),
                    'self_income'=>round($data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0),
                    'life_before_tax' => 0,
                    'life_affter_tax' => 0,
                ];
            }
        }
        return $data;
    }
    /**
     * @param $contract_id
     * @param $self_commission
     * @param int $maxmoney
     * @return bool
     */
    public static function plugIncomeAgencyFirtRevenueMonthly($contract_id,$self_commission,$maxmoney = 50000000){
        $month=  date('m');
        $year= date('Y');
        $data_contract = InsuranceContract::where('id',$contract_id)->first();
        $check_product = Product::where('id',$data_contract->product_id)->first();
        $check_agency =  InsuranceAgency::where('id' , $data_contract->sale_type_id)->first();
        $data_reve  =RevenueMonthly::where([['insurance_agency_id',$data_contract->sale_type_id],['month','=', $month],['year','=',$year]])->first();
        if($check_product->is_life ==1){
            if($check_agency->life_code){
                if($data_reve){
                    $data_reve->life_before_tax = round($data_reve->life_before_tax + $data_contract->require_pay_amount*$self_commission/100*$check_product->PEYP,0);
                    $data_reve->life_affter_tax = round($data_reve->life_affter_tax + ($data_contract->require_pay_amount)*$self_commission/100*$check_product->PEYP*0.95,0) ;
                    $data_reve->self_income = round($data_reve->self_income + ($data_contract->require_pay_amount)*0.95*$self_commission/100*$check_product->PEYP,0)  ;
                    $self_revenue_new = intval($data_reve->self_revenue) + intval(($check_product->is_agency == 1)?$data_contract->require_pay_amount : 0);
                    $data_reve->self_revenue = $self_revenue_new;
                    $data_reve->save();
                }else{
                    RevenueMonthly::create([
                        'insurance_agency_id'=>$data_contract->sale_type_id,
                        'self_revenue'=>($check_product->is_agency == 1)?$data_contract->require_pay_amount:0,
                        'life_before_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0),
                        'life_affter_tax'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0),
                        'self_income'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.95*($self_commission/100),0),
                        'month' => $month,
                        'year' => $year
                    ]);
                }
            }else{
                if($data_reve){
                    $data_reve->life_before_tax = round($data_reve->life_before_tax + $data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0);
                    $data_reve->life_affter_tax = round($data_reve->life_affter_tax + $data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100 ,0)  ;
                    $data_reve->self_income = round($data_reve->self_income + $data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100 , 0) ;
                    $self_revenue_new = intval($data_reve->self_revenue) + intval(($check_product->is_agency == 1)?$data_contract->require_pay_amount : 0);
                    $data_reve->self_revenue = $self_revenue_new;

                    $data_reve->save();
                }else{
                    RevenueMonthly::create([
                        'insurance_agency_id'=>$data_contract->sale_type_id,
                        'self_revenue'=>($check_product->is_agency == 1) ? $data_contract->require_pay_amount:0,
                        'life_before_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0),
                        'life_affter_tax'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100,0),
                        'self_income'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100,0 ),
                        'month' => $month,
                        'year' => $year
                    ]);

                }
            }

        }else{
            if($data_reve){
                $data_reve->nolife_before_tax = round($data_reve->nolife_before_tax+$data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0);
                $self_revenue_new = intval($data_reve->self_revenue) + intval(($check_product->is_agency == 1)?$data_contract->require_pay_amount : 0);
                $data_reve->self_revenue = $self_revenue_new;
                if(intval($data_reve->nolife_affter_tax)+ intval($data_reve->branch_income) <=$maxmoney){
                    $data_reve->nolife_affter_tax = round($data_reve->nolife_affter_tax +$data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0);
                    $data_reve->self_income =round( $data_reve->self_income+ $data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0);
                }else{
                    $data_reve->nolife_affter_tax = round($data_reve->nolife_affter_tax + $data_contract->require_pay_amount*0.9,0);
                    $data_reve->self_income = $data_reve->self_income+ $data_contract->require_pay_amount*0.9*$self_commission/100*$check_product->PEYP;
                }
                $data_reve->save();
            }else{
                RevenueMonthly::create([
                    'insurance_agency_id'=>$data_contract->sale_type_id,
                    'self_revenue'=>($check_product->is_agency == 1)?$data_contract->require_pay_amount:0,
                    'nolife_before_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0),
                    'nolife_affter_tax'=> round($data_contract->require_pay_amoun*$check_product->PEYPt*0.95*$self_commission/100,0),
                    'self_income'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0),
                    'month' => $month,
                    'year' => $year
                ]);
            }
        }
        return true;
    }

    public static function plugIncomeAgencyFirtRevenueMonthlyConvert($month,$contract_id,$self_commission,$maxmoney = 50000000){
        // $month=  date('m');
        $year= date('Y');
        $data_contract = InsuranceContract::where('id',$contract_id)->first();
        $check_product = Product::where('id',$data_contract->product_id)->first();
        $check_agency =  InsuranceAgency::where('id' , $data_contract->sale_type_id)->first();
        $data_reve  =RevenueMonthly::where([['insurance_agency_id',$data_contract->sale_type_id],['month','=', $month],['year','=',$year]])->first();
        if($check_product->is_life ==1){
            if($check_agency->life_code){
                if($data_reve){
                    $data_reve->life_before_tax = round($data_reve->life_before_tax + $data_contract->require_pay_amount*$self_commission/100*$check_product->PEYP,0);
                    $data_reve->life_affter_tax = round($data_reve->life_affter_tax + ($data_contract->require_pay_amount)*$self_commission/100*$check_product->PEYP*0.95,0) ;
                    $data_reve->self_income = round($data_reve->self_income + ($data_contract->require_pay_amount)*0.95*$self_commission/100*$check_product->PEYP,0)  ;
                    $self_revenue_new = intval($data_reve->self_revenue) + intval(($check_product->is_agency == 1)?$data_contract->require_pay_amount : 0);
                    $data_reve->self_revenue = $self_revenue_new;
                    $data_reve->save();
                }else{
                    RevenueMonthly::create([
                        'insurance_agency_id'=>$data_contract->sale_type_id,
                        'self_revenue'=>($check_product->is_agency == 1)?$data_contract->require_pay_amount:0,
                        'life_before_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0),
                        'life_affter_tax'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0),
                        'self_income'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.95*($self_commission/100),0),
                        'month' => $month,
                        'year' => $year
                    ]);
                }
            }else{
                if($data_reve){
                    $data_reve->life_before_tax = round($data_reve->life_before_tax + $data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0);
                    $data_reve->life_affter_tax = round($data_reve->life_affter_tax + $data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100 ,0)  ;
                    $data_reve->self_income = round($data_reve->self_income + $data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100 , 0) ;
                    $self_revenue_new = intval($data_reve->self_revenue) + intval(($check_product->is_agency == 1)?$data_contract->require_pay_amount : 0);
                    $data_reve->self_revenue = $self_revenue_new;

                    $data_reve->save();
                }else{
                    RevenueMonthly::create([
                        'insurance_agency_id'=>$data_contract->sale_type_id,
                        'self_revenue'=>/*($check_product->is_agency == 1) ?*/ $data_contract->id/*:0*/,
                        'life_before_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0),
                        'life_affter_tax'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100,0),
                        'self_income'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100,0 ),
                        'month' => $month,
                        'year' => $year
                    ]);

                }
            }

        }else{
            if($data_reve){
                $data_reve->nolife_before_tax = round($data_reve->nolife_before_tax+$data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0);
                $self_revenue_new = intval($data_reve->self_revenue) + intval(($check_product->is_agency == 1)?$data_contract->require_pay_amount : 0);
                $data_reve->self_revenue = $self_revenue_new;
                if(intval($data_reve->nolife_affter_tax)+ intval($data_reve->branch_income) <=$maxmoney){
                    $data_reve->nolife_affter_tax = round($data_reve->nolife_affter_tax +$data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0);
                    $data_reve->self_income =round( $data_reve->self_income+ $data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0);
                }else{
                    $data_reve->nolife_affter_tax = round($data_reve->nolife_affter_tax + $data_contract->require_pay_amount*0.9,0);
                    $data_reve->self_income = $data_reve->self_income+ $data_contract->require_pay_amount*0.9*$self_commission/100*$check_product->PEYP;
                }
                $data_reve->save();
            }else{
                RevenueMonthly::create([
                    'insurance_agency_id'=>$data_contract->sale_type_id,
                    'self_revenue'=>($check_product->is_agency == 1)?$data_contract->require_pay_amount:0,
                    'nolife_before_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0),
                    'nolife_affter_tax'=> round($data_contract->require_pay_amoun*$check_product->PEYPt*0.95*$self_commission/100,0),
                    'self_income'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0),
                    'month' => $month,
                    'year' => $year
                ]);
            }
        }
        return true;
    }
    public static function plugIncomeAgencyFirtRevenueMonthlyConvertDay($month,$contract_id,$self_commission,$maxmoney = 50000000){
        // $month=  date('m');
        $data_contract = InsuranceContract::where('id',$contract_id)->first();
        $year = date('Y', strtotime($data_contract->date_signed));
        $check_product = Product::where('id',$data_contract->product_id)->first();
        $check_agency =  InsuranceAgency::where('id' , $data_contract->sale_type_id)->first();
        $data_reve  =RevenueMonthly::where([['insurance_agency_id',$data_contract->sale_type_id],['month','=', $month],['year','=',$year]])->first();
        if($check_product->is_life ==1){
            if($check_agency->life_code){
                if($data_reve){
                    $data_reve->life_before_tax = round($data_reve->life_before_tax + $data_contract->require_pay_amount*$self_commission/100*$check_product->PEYP,0);
                    $data_reve->life_affter_tax = round($data_reve->life_affter_tax + ($data_contract->require_pay_amount)*$self_commission/100*$check_product->PEYP*0.95,0) ;
                    $data_reve->self_income = round($data_reve->self_income + ($data_contract->require_pay_amount)*0.95*$self_commission/100*$check_product->PEYP,0)  ;
                    $self_revenue_new = intval($data_reve->self_revenue) + intval(($check_product->is_agency == 1)?$data_contract->require_pay_amount : 0);
                    $data_reve->self_revenue = $self_revenue_new;
                    $data_reve->save();
                }else{
                    RevenueMonthly::create([
                        'insurance_agency_id'=>$data_contract->sale_type_id,
                        'self_revenue'=>($check_product->is_agency == 1)?$data_contract->require_pay_amount:0,
                        'life_before_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0),
                        'life_affter_tax'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0),
                        'self_income'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.95*($self_commission/100),0),
                        'month' => $month,
                        'year' => $year
                    ]);
                }
            }else{
                if($data_reve){
                    $data_reve->life_before_tax = round($data_reve->life_before_tax + $data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0);
                    $data_reve->life_affter_tax = round($data_reve->life_affter_tax + $data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100 ,0)  ;
                    $data_reve->self_income = round($data_reve->self_income + $data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100 , 0) ;
                    $self_revenue_new = intval($data_reve->self_revenue) + intval(($check_product->is_agency == 1)?$data_contract->require_pay_amount : 0);
                    $data_reve->self_revenue = $self_revenue_new;

                    $data_reve->save();
                }else{
                    RevenueMonthly::create([
                        'insurance_agency_id'=>$data_contract->sale_type_id,
                        'self_revenue'=>/*($check_product->is_agency == 1) ?*/ $data_contract->id/*:0*/,
                        'life_before_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0),
                        'life_affter_tax'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100,0),
                        'self_income'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100,0 ),
                        'month' => $month,
                        'year' => $year
                    ]);

                }
            }

        }else{
            if($data_reve){
                $data_reve->nolife_before_tax = round($data_reve->nolife_before_tax+$data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0);
                $self_revenue_new = intval($data_reve->self_revenue) + intval(($check_product->is_agency == 1)?$data_contract->require_pay_amount : 0);
                $data_reve->self_revenue = $self_revenue_new;
                if(intval($data_reve->nolife_affter_tax)+ intval($data_reve->branch_income) <=$maxmoney){
                    $data_reve->nolife_affter_tax = round($data_reve->nolife_affter_tax +$data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0);
                    $data_reve->self_income =round( $data_reve->self_income+ $data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0);
                }else{
                    $data_reve->nolife_affter_tax = round($data_reve->nolife_affter_tax + $data_contract->require_pay_amount*0.9,0);
                    $data_reve->self_income = $data_reve->self_income+ $data_contract->require_pay_amount*0.9*$self_commission/100*$check_product->PEYP;
                }
                $data_reve->save();
            }else{
                RevenueMonthly::create([
                    'insurance_agency_id'=>$data_contract->sale_type_id,
                    'self_revenue'=>($check_product->is_agency == 1)?$data_contract->require_pay_amount:0,
                    'nolife_before_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0),
                    'nolife_affter_tax'=> round($data_contract->require_pay_amoun*$check_product->PEYPt*0.95*$self_commission/100,0),
                    'self_income'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0),
                    'month' => $month,
                    'year' => $year
                ]);
            }
        }
        return true;
    }


    public static function plugIncomeAgencyFirtRevenueDaylyConvertDay_date($month,$contract_id,$self_commission,$maxmoney = 50000000){
        // $month=  date('m');
        $date = date('Y-m-d');
        $date_1 = date('Y-m-d', strtotime($date. ' - 1 days'));
        $ngay = date('d',$date_1);
        $year= date('Y');
        $data_contract = InsuranceContract::where('id',$contract_id)->first();
        $check_product = Product::where('id',$data_contract->product_id)->first();
        $check_agency =  InsuranceAgency::where('id' , $data_contract->sale_type_id)->first();
        $data_reve  =RevenueDayly::where([['insurance_agency_id',$data_contract->sale_type_id],['month','=', $month],['year','=',$year]])->first();
        if($check_product->is_life ==1){
            if($check_agency->life_code){
                if($data_reve){
                    $data_reve->life_before_tax = round($data_reve->life_before_tax + $data_contract->require_pay_amount*$self_commission/100*$check_product->PEYP,0);
                    $data_reve->life_affter_tax = round($data_reve->life_affter_tax + ($data_contract->require_pay_amount)*$self_commission/100*$check_product->PEYP*0.95,0) ;
                    $data_reve->self_income = round($data_reve->self_income + ($data_contract->require_pay_amount)*0.95*$self_commission/100*$check_product->PEYP,0)  ;
                    $self_revenue_new = intval($data_reve->self_revenue) + intval(($check_product->is_agency == 1)?$data_contract->require_pay_amount : 0);
                    $data_reve->self_revenue = $self_revenue_new;
                    $data_reve->save();
                }else{
                    RevenueDayly::create([
                        'insurance_agency_id'=>$data_contract->sale_type_id,
                        'self_revenue'=>($check_product->is_agency == 1)?$data_contract->require_pay_amount:0,
                        'life_before_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0),
                        'life_affter_tax'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0),
                        'self_income'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.95*($self_commission/100),0),
                        'month' => $month,
                        'year' => $year,
                        'day' => $ngay
                    ]);
                }
            }else{
                if($data_reve){
                    $data_reve->life_before_tax = round($data_reve->life_before_tax + $data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0);
                    $data_reve->life_affter_tax = round($data_reve->life_affter_tax + $data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100 ,0)  ;
                    $data_reve->self_income = round($data_reve->self_income + $data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100 , 0) ;
                    $self_revenue_new = intval($data_reve->self_revenue) + intval(($check_product->is_agency == 1)?$data_contract->require_pay_amount : 0);
                    $data_reve->self_revenue = $self_revenue_new;

                    $data_reve->save();
                }else{
                    RevenueDayly::create([
                        'insurance_agency_id'=>$data_contract->sale_type_id,
                        'self_revenue'=>/*($check_product->is_agency == 1) ?*/ $data_contract->id/*:0*/,
                        'life_before_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0),
                        'life_affter_tax'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100,0),
                        'self_income'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100,0 ),
                        'month' => $month,
                        'year' => $year,
                        'day' => $ngay
                    ]);

                }
            }
        }else{
            if($data_reve){
                $data_reve->nolife_before_tax = round($data_reve->nolife_before_tax+$data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0);
                $self_revenue_new = intval($data_reve->self_revenue) + intval(($check_product->is_agency == 1)?$data_contract->require_pay_amount : 0);
                $data_reve->self_revenue = $self_revenue_new;
                if(intval($data_reve->nolife_affter_tax)+ intval($data_reve->branch_income) <=$maxmoney){
                    $data_reve->nolife_affter_tax = round($data_reve->nolife_affter_tax +$data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0);
                    $data_reve->self_income =round( $data_reve->self_income+ $data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0);
                }else{
                    $data_reve->nolife_affter_tax = round($data_reve->nolife_affter_tax + $data_contract->require_pay_amount*0.9,0);
                    $data_reve->self_income = $data_reve->self_income+ $data_contract->require_pay_amount*0.9*$self_commission/100*$check_product->PEYP;
                }
                $data_reve->save();
            }else{
                RevenueDayly::create([
                    'insurance_agency_id'=>$data_contract->sale_type_id,
                    'self_revenue'=>($check_product->is_agency == 1)?$data_contract->require_pay_amount:0,
                    'nolife_before_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0),
                    'nolife_affter_tax'=> round($data_contract->require_pay_amoun*$check_product->PEYPt*0.95*$self_commission/100,0),
                    'self_income'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.95*$self_commission/100,0),
                    'month' => $month,
                    'year' => $year,
                    'day' => $ngay
                ]);
            }
        }
        return true;
    }

    /**
     * @param $contract
     * @param $agency
     * @param $check_firt
     * @param $i
     */
    public static  function plusAgencyParentRevenueContract($contract,$agency,$check_firt,$i){
        $product = Product::where('id',$contract->product_id)->first();
        $level_product_child = db::table('level_product')->where([['level_id', $agency->level_id], ['product_id', $contract->product_id]])->first();
        $child_commission_rate = $level_product_child->commission_rate;
        if($check_firt){
            $self_income_affter_tax = InsuranceContract::incomeTax($agency->id,$contract->id,$child_commission_rate);
            revenuecontract::create([
                'agency_type' => $contract->sale_type,
                'insurance_agency_id' => $contract->sale_type_id,
                'product_id' => $contract->product_id,
                'contract_value' => $contract->require_pay_amount,
                'self_income' => $self_income_affter_tax['self_income'],
                'branch_income' => 0,
                'child_agency_id' => 0,
                'self_commission' => $child_commission_rate,
                'contract_id' => $contract->id,
                'start_time' => $contract->start_time,
                'end_time' => $contract->end_time,
                'is_system' => $product->is_agency,
                'branch_commission' => 0,
                'date_signed' => date('y-m-d h:i:s'),
            ]);
        }
        $record = insuranceagency::find($agency->parent_id);
        if (revenue::where('isurance_agency_id', $agency->parent_id)->first() != null) {
            $branch_revenue_old = 0;
            if ($record){
                $branch_revenue_old = $record->revenue->low_level_revenue;
            }
            revenue::where('isurance_agency_id', $agency->parent_id)->update([
                'low_level_revenue' => $branch_revenue_old + $contract->require_pay_amount
            ]);
        }
        else {
            revenue::create([
                'isurance_agency_id' => $agency->parent_id,
                'personal_income' => 0,
                'low_level_revenue' => 0,
            ]);
            $branch_revenue_old = $record->revenue->low_level_revenue;
            revenue::where('isurance_agency_id', $agency->parent_id)->update([
                'low_level_revenue' => $branch_revenue_old + $contract->require_pay_amount
            ]);
        }
        if($agency->parent_id > 0){
            $parent__agency = InsuranceAgency::where('id',$agency->parent_id)->first();
            $level_parent = $parent__agency->level_id;
            $level_child = $agency->level_id;
            $level_product_parent = db::table('level_product')->where([['level_id', $parent__agency->level_id], ['product_id', $contract->product_id]])->first();
            $parent_commission_rate = $level_product_parent->commission_rate;
            $branch_commission = 0 ;
            if($parent_commission_rate  > $child_commission_rate){
                $branch_commission = $parent_commission_rate - $child_commission_rate;
            }

            $branch_income = 0 ;
            if($level_child < $level_parent ) {
                $branch_income = InsuranceContract::incomeTax($agency->id,$contract->id, $branch_commission )['self_income'] ;
            }else if($level_child = $level_parent){
                $branch_income= InsuranceContract::incomeTax($agency->id,$contract->id, $level_product_parent->counterpart_commission_rate )['self_income'];
            }
            $lever_par = $parent__agency->level->level;
            revenuecontract::create([
                'agency_type' => $contract->sale_type,
                'insurance_agency_id' => $agency->parent_id,
                'product_id' => $contract->product_id,
                'contract_value' => 0,
                'self_income' => 0,
                'child_agency_id' => $agency->id,
                'self_commission' => 0,
                'branch_income' => $branch_income,
                'contract_id' => $contract->id,
                'start_time' => $contract->start_time,
                'end_time' => $contract->end_time,
                'is_system' => $product->is_agency,
                'branch_commission' => $agency->level->level == $lever_par ?$level_product_parent->counterpart_commission_rate:$branch_commission,
                'date_signed' => date('y-m-d h:i:s'),
            ]);
            if($i <= 50 && $parent__agency->parent_id > 0){
                $i++;
                InsuranceContract::plusAgencyParentRevenueContract($contract,$parent__agency,$check_firt=false,$i);
            }
        }
    }

    /**
     * @param $contract hợp đồng
     * @param $agency đại lý
    rent = $parent__agency->level_id; @param $check_firt giá trị check lần  đàu
     * @param $i fix lỗi dữ liệu người dùng
     */
//    public static  function plusAgencyParentRevenuesMonth($contract,$agency,$i){
//        $month = date('m');
//        $product = Product::where('id',$contract->product_id)->first();
//        if($agency->parent_id !=0 && $product->is_agency == 1){
//            //    if($i == 0){
//            //    print_r($contract->id.'-'.$contract->require_pay_amount.'--'.$agency->id.'<br>');
//            //    }
//            $parent__agency = InsuranceAgency::where('id',$agency->parent_id)->first();
//            $level_parent = $parent__agency->level_id;
//            $level_child = $agency->level_id;
//            $level_product_parent = db::table('level_product')->where([['level_id', $level_parent], ['product_id', $contract->product_id]])->first();
//            $level_product_child = db::table('level_product')->where([['level_id', $agency->level_id], ['product_id', $contract->product_id]])->first();
//            $child_commission_rate = $level_product_child->commission_rate;
//            $parent_commission_rate = 0;
//            if(isset($level_product_parent)&& $level_product_parent != null){
//                $parent_commission_rate = $level_product_parent->commission_rate;
//            }
//            $branch_commission = 0 ;
//            if($parent_commission_rate  > $child_commission_rate){
//                $branch_commission = $parent_commission_rate - $child_commission_rate;
//            }
//            $money_affter_tax = 0 ;
//            if($level_child < $level_parent ) {
//                $money_affter_tax = InsuranceContract::incomeTax($agency->id,$contract->id, $branch_commission ) ;
//            }else if($level_child = $level_parent){
//                $money_affter_tax= InsuranceContract::incomeTax($agency->id,$contract->id, $level_product_parent->counterpart_commission_rate );
//            }
//            $revenue_month = revenuemonthly::where([ ['insurance_agency_id','=', $agency->parent_id], ['month','=',$month], ['year','=', date('Y')]])->first();
//            if($revenue_month != null){
//                $chec = $revenue_month->update([
//                    'branch_revenue' => (int)$revenue_month->branch_revenue + (int)$contract->require_pay_amount,
//                    'branch_income' => (int)($revenue_month->branch_income) + (int)(($money_affter_tax['self_income'])?$money_affter_tax['self_income']:0),
//                    'nolife_before_tax' => $revenue_month ->nolife_before_tax + ($money_affter_tax['nolife_before_tax'])?$money_affter_tax['nolife_before_tax']:0,
//                    'nolife_affter_tax' => $revenue_month ->nolife_affter_tax + ($money_affter_tax['nolife_affter_tax'])?$money_affter_tax['nolife_affter_tax']:0,
//                    'life_before_tax' => $revenue_month ->life_before_tax + ($money_affter_tax['life_before_tax'])?$money_affter_tax['life_before_tax']:0,
//                    'life_affter_tax' => $revenue_month ->life_affter_tax + ($money_affter_tax['life_affter_tax'])?$money_affter_tax['life_affter_tax']:0,
//                ]);
//                if($chec != true){
//                    dd($contract->id);
//                }
//            }else{
//                $check2 = revenuemonthly::create([
//                    'insurance_agency_id' => $agency->parent_id,
//                    'self_revenue' => 0,
//                    'branch_revenue' => $contract->require_pay_amount,
//                    'self_income' => 0,
//                    'branch_income' => ($money_affter_tax['self_income'])?$money_affter_tax['self_income']:0,
//                    'nolife_before_tax' =>($money_affter_tax['nolife_before_tax'])? $money_affter_tax['nolife_before_tax']:0,
//                    'nolife_affter_tax' => ($money_affter_tax['nolife_affter_tax'])?$money_affter_tax['nolife_affter_tax']:0,
//                    'life_before_tax' => ($money_affter_tax['life_before_tax'])?$money_affter_tax['life_before_tax']:0,
//                    'life_affter_tax' => ($money_affter_tax['life_affter_tax'])?$money_affter_tax['life_affter_tax']:0,
//                    'month' => date('m'),
//                    'year' => date('Y')
//                ]);
//                if($check2 == null){
//                    dd($contract->id);
//                }
//            }
//            if($i < 100 && $parent__agency->parent_id != 0){
//                $i++;
//                InsuranceContract::plusAgencyParentRevenuesMonth($contract,$parent__agency,$i);
//            }
//        }
//    }
    public static  function plusAgencyParentRevenuesConvertDay($month, $contract,$agency,$i){
        $product = Product::where('id',$contract->product_id)->first();
        if($agency->parent_id !=0 && $product->is_agency == 1){
            if($i == 0){
                print_r($contract->id.'-'.$contract->require_pay_amount.'--'.$agency->id.'<br>');
            }
            $parent__agency = InsuranceAgency::where('id',$agency->parent_id)->first();
            $level_parent = $parent__agency->level_id;
            $level_child = $agency->level_id;
            $level_product_parent = db::table('level_product')->where([['level_id', $level_parent], ['product_id', $contract->product_id]])->first();
            $level_product_child = db::table('level_product')->where([['level_id', $agency->level_id], ['product_id', $contract->product_id]])->first();
            $child_commission_rate = $level_product_child->commission_rate;
            $parent_commission_rate = 0;
            if(isset($level_product_parent)&& $level_product_parent != null){
                $parent_commission_rate = $level_product_parent->commission_rate;
            }
            $branch_commission = 0 ;
            if($parent_commission_rate  > $child_commission_rate){
                $branch_commission = $parent_commission_rate -$child_commission_rate;
            }
            if($level_child < $level_parent ) {
                $money_affter_tax = InsuranceContract::incomeTax($agency->id,$contract->id, $branch_commission );
            }else if($level_child = $level_parent){
                $money_affter_tax= InsuranceContract::incomeTax($agency->id,$contract->id, $level_product_parent->counterpart_commission_rate );
            }
            $revenue_month = revenuemonthly::where([ ['insurance_agency_id','=', $agency->parent_id], ['month','=',$month], ['year','=', date('Y')]])->first();
            if($revenue_month != null){
                $chec = $revenue_month->update([
                    'branch_revenue' => (int)$revenue_month->branch_revenue + (int)$contract->require_pay_amount,
                    'branch_income' => (int)($revenue_month->branch_income) + (int)(($money_affter_tax['self_income'])?$money_affter_tax['self_income']:0),
                    'nolife_before_tax' => (int)$revenue_month ->nolife_before_tax + (int)($money_affter_tax['nolife_before_tax'])?$money_affter_tax['nolife_before_tax']:0,
                    'nolife_affter_tax' => (int)$revenue_month ->nolife_affter_tax + (int)($money_affter_tax['nolife_affter_tax'])?$money_affter_tax['nolife_affter_tax']:0,
                    'life_before_tax' => (int)$revenue_month ->life_before_tax + (int)($money_affter_tax['life_before_tax'])?$money_affter_tax['life_before_tax']:0,
                    'life_affter_tax' => (int)$revenue_month ->life_affter_tax + (int)($money_affter_tax['life_affter_tax'])?$money_affter_tax['life_affter_tax']:0,
                ]);
                if($chec != true){
                    dd($contract->id);
                }
            }else{
                $check2 = revenuemonthly::create([
                    'insurance_agency_id' => $agency->parent_id,
                    'self_revenue' => 0,
                    'branch_revenue' => $contract->require_pay_amount,
                    'self_income' => 0,
                    'branch_income' => ($money_affter_tax['self_income'])?$money_affter_tax['self_income']:0,
                    'nolife_before_tax' =>($money_affter_tax['nolife_before_tax'])? $money_affter_tax['nolife_before_tax']:0,
                    'nolife_affter_tax' => ($money_affter_tax['nolife_affter_tax'])?$money_affter_tax['nolife_affter_tax']:0,
                    'life_before_tax' => ($money_affter_tax['life_before_tax'])?$money_affter_tax['life_before_tax']:0,
                    'life_affter_tax' => ($money_affter_tax['life_affter_tax'])?$money_affter_tax['life_affter_tax']:0,
                    'month' => $month,
                    'year' =>  date('Y', strtotime($contract->date_signed))
                ]);
                if($check2 == null){
                    dd($contract->id);
                }
            }
            if($i < 100 && $parent__agency->parent_id != 0){
                $i++;
                InsuranceContract::plusAgencyParentRevenuesConvertDay($month,$contract,$parent__agency,$i);
            }
        }
    }

//    public static  function plusAgencyParentRevenuesDaylyConvertDay_date($month, $contract,$agency,$i){
//        $date = date('Y-m-d');
//        $date_1 = date('Y-m-d', strtotime($date. ' - 1 days'));
//        $ngay = date('d',$date_1);
//        $product = Product::where('id',$contract->product_id)->first();
//        if($agency->parent_id !=0 && $product->is_agency == 1){
//            if($i == 0){
//                print_r($contract->id.'-'.$contract->require_pay_amount.'--'.$agency->id.'<br>');
//            }
//            $parent__agency = InsuranceAgency::where('id',$agency->parent_id)->first();
//            $level_parent = $parent__agency->level_id;
//            $level_child = $agency->level_id;
//            $level_product_parent = db::table('level_product')->where([['level_id', $level_parent], ['product_id', $contract->product_id]])->first();
//            $level_product_child = db::table('level_product')->where([['level_id', $agency->level_id], ['product_id', $contract->product_id]])->first();
//            $child_commission_rate = $level_product_child->commission_rate;
//            $parent_commission_rate = 0;
//            if(isset($level_product_parent)&& $level_product_parent != null){
//                $parent_commission_rate = $level_product_parent->commission_rate;
//            }
//            $branch_commission = 0 ;
//            if($parent_commission_rate  > $child_commission_rate){
//                $branch_commission = $parent_commission_rate -$child_commission_rate;
//            }
//            if($level_child < $level_parent ) {
//                $money_affter_tax = InsuranceContract::incomeTax($agency->id,$contract->id, $branch_commission );
//            }else if($level_child = $level_parent){
//                $money_affter_tax= InsuranceContract::incomeTax($agency->id,$contract->id, $level_product_parent->counterpart_commission_rate );
//            }
//            $revenue_month = RevenueDayly::where([ ['insurance_agency_id','=', $agency->parent_id], ['month','=',$month], ['year','=', date('Y')]])->first();
//            if($revenue_month != null){
//                $chec = $revenue_month->update([
//                    'branch_revenue' => (int)$revenue_month->branch_revenue + (int)$contract->require_pay_amount,
//                    'branch_income' => (int)($revenue_month->branch_income) + (int)(($money_affter_tax['self_income'])?$money_affter_tax['self_income']:0),
//                    'nolife_before_tax' => (int)$revenue_month ->nolife_before_tax + (int)($money_affter_tax['nolife_before_tax'])?$money_affter_tax['nolife_before_tax']:0,
//                    'nolife_affter_tax' => (int)$revenue_month ->nolife_affter_tax + (int)($money_affter_tax['nolife_affter_tax'])?$money_affter_tax['nolife_affter_tax']:0,
//                    'life_before_tax' => (int)$revenue_month ->life_before_tax + (int)($money_affter_tax['life_before_tax'])?$money_affter_tax['life_before_tax']:0,
//                    'life_affter_tax' => (int)$revenue_month ->life_affter_tax + (int)($money_affter_tax['life_affter_tax'])?$money_affter_tax['life_affter_tax']:0,
//                ]);
//                if($chec != true){
//                    dd($contract->id);
//                }
//            }else{
//                $check2 = RevenueDayly::create([
//                    'insurance_agency_id' => $agency->parent_id,
//                    'self_revenue' => 0,
//                    'branch_revenue' => $contract->require_pay_amount,
//                    'self_income' => 0,
//                    'branch_income' => ($money_affter_tax['self_income'])?$money_affter_tax['self_income']:0,
//                    'nolife_before_tax' =>($money_affter_tax['nolife_before_tax'])? $money_affter_tax['nolife_before_tax']:0,
//                    'nolife_affter_tax' => ($money_affter_tax['nolife_affter_tax'])?$money_affter_tax['nolife_affter_tax']:0,
//                    'life_before_tax' => ($money_affter_tax['life_before_tax'])?$money_affter_tax['life_before_tax']:0,
//                    'life_affter_tax' => ($money_affter_tax['life_affter_tax'])?$money_affter_tax['life_affter_tax']:0,
//                    'month' => $month,
//                    'year' => date('Y'),
//                    'day' => $ngay
//                ]);
//                if($check2 == null){
//                    dd($contract->id);
//                }
//            }
//            if($i < 100 && $parent__agency->parent_id != 0){
//                $i++;
//                InsuranceContract::plusAgencyParentRevenuesDaylyConvertDay_date($month,$contract,$parent__agency,$i);
//            }
//        }
//    }

    public static  function plusAgencyParentRevenuesConvert($month, $contract,$agency,$i){
        $product = Product::where('id',$contract->product_id)->first();
        if($agency->parent_id !=0 && $product->is_agency == 1){
            if($i == 0){
                print_r($contract->id.'-'.$contract->require_pay_amount.'--'.$agency->id.'<br>');
            }
            $parent__agency = InsuranceAgency::where('id',$agency->parent_id)->first();
            $level_parent = $parent__agency->level_id;
            $level_child = $agency->level_id;
            $level_product_parent = db::table('level_product')->where([['level_id', $level_parent], ['product_id', $contract->product_id]])->first();
            $level_product_child = db::table('level_product')->where([['level_id', $agency->level_id], ['product_id', $contract->product_id]])->first();
            $child_commission_rate = $level_product_child->commission_rate;
            $parent_commission_rate = 0;
            if(isset($level_product_parent)&& $level_product_parent != null){
                $parent_commission_rate = $level_product_parent->commission_rate;
            }
            $branch_commission = 0 ;
            if($parent_commission_rate  > $child_commission_rate){
                $branch_commission = $parent_commission_rate -$child_commission_rate;
            }
            if($level_child < $level_parent ) {
                $money_affter_tax = InsuranceContract::incomeTax($agency->id,$contract->id, $branch_commission );
            }else if($level_child = $level_parent){
                $money_affter_tax= InsuranceContract::incomeTax($agency->id,$contract->id, $level_product_parent->counterpart_commission_rate );
            }
            $revenue_month = revenuemonthly::where([ ['insurance_agency_id','=', $agency->parent_id], ['month','=',$month] ,['year','=', date('Y')]])->first();
            if($revenue_month != null){
                $chec = $revenue_month->update([
                    'branch_revenue' => (int)$revenue_month->branch_revenue + (int)$contract->require_pay_amount,
                    'branch_income' => (int)($revenue_month->branch_income) + (int)(($money_affter_tax['self_income'])?$money_affter_tax['self_income']:0),
                    'nolife_before_tax' => (int)$revenue_month ->nolife_before_tax + (int)($money_affter_tax['nolife_before_tax'])?$money_affter_tax['nolife_before_tax']:0,
                    'nolife_affter_tax' => (int)$revenue_month ->nolife_affter_tax + (int)($money_affter_tax['nolife_affter_tax'])?$money_affter_tax['nolife_affter_tax']:0,
                    'life_before_tax' => (int)$revenue_month ->life_before_tax + (int)($money_affter_tax['life_before_tax'])?$money_affter_tax['life_before_tax']:0,
                    'life_affter_tax' => (int)$revenue_month ->life_affter_tax + (int)($money_affter_tax['life_affter_tax'])?$money_affter_tax['life_affter_tax']:0,
                ]);
                if($chec != true){
                    dd($contract->id);
                }
            }else{
                $check2 = revenuemonthly::create([
                    'insurance_agency_id' => $agency->parent_id,
                    'self_revenue' => 0,
                    'branch_revenue' => $contract->require_pay_amount,
                    'self_income' => 0,
                    'branch_income' => ($money_affter_tax['self_income'])?$money_affter_tax['self_income']:0,
                    'nolife_before_tax' =>($money_affter_tax['nolife_before_tax'])? $money_affter_tax['nolife_before_tax']:0,
                    'nolife_affter_tax' => ($money_affter_tax['nolife_affter_tax'])?$money_affter_tax['nolife_affter_tax']:0,
                    'life_before_tax' => ($money_affter_tax['life_before_tax'])?$money_affter_tax['life_before_tax']:0,
                    'life_affter_tax' => ($money_affter_tax['life_affter_tax'])?$money_affter_tax['life_affter_tax']:0,
                    'month' => $month,
                    'year' => date('Y')
                ]);
                if($check2 == null){
                    dd($contract->id);
                }
            }
            if($i < 100 && $parent__agency->parent_id != 0){
                $i++;
                InsuranceContract::plusAgencyParentRevenuesConvert($month,$contract,$parent__agency,$i);
            }
        }
    }
    /**
     * @param $id
     * @param $contract
     * @param $product
     * @param $agency
     * @param $agency_parent
     */
    public static function updatePaymentOffline($id, $contract, $product, $agency, $agency_parent){
        Log::info('[Log_revenue] hop dong: '.$id.'bat dau ham doanh thu');
        // Cộng doanh thu cá nhân
        // Doanh thu cũ
        if ($agency->revenue == null) {
            Revenue::create([
                'isurance_agency_id' => $contract->sale_type_id,
                'personal_income' => 0,
                'low_level_revenue' => 0,
            ]);
            // Cộng danh thu cá nhân
            Revenue::where('isurance_agency_id', $contract->sale_type_id)->update([
                'personal_income' => $contract->require_pay_amount
            ]);
        }
        else {
            $revenue_old = $agency->revenue->personal_income;
            // Cộng danh thu cá nhân
            Revenue::where('isurance_agency_id', $contract->sale_type_id)->update([
                'personal_income' => $revenue_old + $contract->require_pay_amount
            ]);
        }
        $level_product = DB::table('level_product')->where([['level_id', $agency->level->id], ['product_id', $contract->product_id]])->first();
        $self_commission = $level_product->commission_rate;
//        $self_income = $contract->require_pay_amount * $product->PEYP * $self_commission / 100 ;
        // Cong luong ca  nhan vao vi
//        $self_incombefore_tax = InsuranceContract::incomeTax($agency->id,$id,$self_commission);
//        InsuranceContract::plusSalaryAgency($agency->id,$contract->id,$self_incombefore_tax['self_income']);
//        InsuranceContract::plugIncomeAgencyFirtRevenueMonthly($id,$self_commission);
//        InsuranceContract::plusAgencyParentRevenueContract($contract,$agency,$check_firt = true, $i=0);
//        InsuranceContract::plusAgencyParentRevenuesMonth($contract,$agency, $i=0);

    }
    public static function updateDataPti($contract){
        $curl = curl_init();
        $data = array(
            'ngay_ht' => date('Ymd',strtotime(json_decode($contract['date_signed']))),
            'so_id_kenh_api' => $contract['id'],
            'so_id_gcn_daky' => $contract['code'],
            'so_hd' => $contract['contract_number'],
            'kieu_hd' => 'G',
            'ma_nv' => 'BATG',
            'dvi_sl' => '038',
            'kenh' => 'MOONCOVER',
            'ten_dn' => json_decode($contract['customer_detail'])->name,
            'dchi_dn'=>json_decode($contract['customer_detail'])->address,
            'ttrang' => 'D',
            'ttoan' => $contract['require_pay_amount'],
            'ma_khoi'=>''

        );
        if($contract['product_code'] ==  'BATG'){
            $goi = 'GOI1';
            $magoi = 'BAC';
        }elseif ($contract['product_code'] ==  'BATG1'){
            $goi = 'GOI2';
            $magoi = 'VANG';
        }else{
            $goi = 'GOI3';
            $magoi = 'BK';
        }
        $ds_dk = array(
            array(
                'so_id_dt_ds'=>$contract['code'],
                'ten'=>json_decode($contract['customer_detail'])->name,
                'ngay_sinh'=>date('Ymd',strtotime(json_decode($contract['customer_detail'])->date_of_birth)),
                'so_cmt'=>json_decode($contract['customer_detail'])->identity_card,
                'phone'=>json_decode($contract['customer_detail'])->phone_number,
                'email'=>json_decode($contract['customer_detail'])->email,
                'qhe'=>json_decode($contract['customer_detail'])->name,
                'dchi'=>json_decode($contract['customer_detail'])->address,
                'ngay_hl'=>date("d/m/Y", strtotime($contract['date_signed'])),
                'goi'=>$goi,
                'so_thang_bh'=>12,
                'ma_goi'=>$magoi,
            )
        );
        $total  = [
            'data'=>json_encode($data,JSON_UNESCAPED_UNICODE),
            'ds_dk'=>json_encode($ds_dk,JSON_UNESCAPED_UNICODE),
            'cot'=>json_encode('',JSON_UNESCAPED_UNICODE),
            'phi'=>json_encode('',JSON_UNESCAPED_UNICODE),
            'ds_tra'=>json_encode('',JSON_UNESCAPED_UNICODE),
            'encrypt'=>json_encode('',JSON_UNESCAPED_UNICODE),
        ];
        dd($total);
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://betaapi.pti.com.vn/api/NGUOI/KENH_API_NGUOI_NH",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>json_encode($total,JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => array(
                "UserName: vpbank.rb@api",
                "Password: b9c48194a14e95a1af8f43b83a491138",
                "SecretKey: 71D6F076D91B5F8234E7B496273237E7",
                "Content-Type: application/json",
                "charset: UTF-8"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public static function getStatusContract(){
        return $status = [
            InsuranceContract::NEW_CONTRACT =>  'Hợp đồng mới',
            InsuranceContract::RENEW_CONTRACT => 'Hợp đồng tái tục'
        ];
    }
}


