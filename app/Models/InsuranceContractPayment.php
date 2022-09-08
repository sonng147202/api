<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsuranceContractPayment extends Model
{
    protected $fillable = [
        'contract_id','amount','pay_type', 'pay_type_detail', 'payment_fee','payment_time','payment_id','payment_detail',
        'created_by','created_at','updated_at', 'status' , 'account_payment'
    ];

    const PAY_TYPE = [0 => 'Tiền mặt', 1 => 'Thanh toán online', 2 => 'Chuyển khoản', /*3 => 'Ví điện tử', */4 => 'Chuyển khoản CTBH', /*5 => 'Thanh toán bằng ví'*/];
    const STATUS_CONFIRM = 1;
    const ACCOUNT_PAYMENT = [1 => 'VCB VVT', 2 => 'VCB EBH', 3 => 'VCB EROS', 4 => 'VPB EBH', 5 => 'VPB EROS', 6 => 'GPB LHK'];
    public function insurance_contract()
    {
        return $this->belongsTo('App\Models\InsuranceContract','contract_id');
    }
    
    /**
     * Get pay type
     *
     * @param $pay_type
     * @return mixed|string
     */
    public static function getNamePaymentType($pay_type){
        foreach (self::PAY_TYPE as $key=>$value){
            if($key == $pay_type){
                return $value;
            }
        }
        return "";
    }

    /**
     * @param $account_type
     * @return mixed|string
     */
    public static function getNamePaymentAccount($account_type) {
        foreach (self::ACCOUNT_PAYMENT as $key=>$value){
            if($key == $account_type){
                return $value;
            }
        }
        return "";
    }

    /**
     * @param $contractId
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getByContract($contractId)
    {
        return self::where('contract_id', $contractId)->get();
    }

    /**
     * @param $paymentIds
     * @return bool
     */
    public static function confirmPayment($paymentIds)
    {
         return self::whereIn('id', $paymentIds)->update(['status' => self::STATUS_CONFIRM]);
    }

    public static function getAmount($paymentIds)
    {
        return self::whereIn('id', $paymentIds)->sum('amount');
    }
}
