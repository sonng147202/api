<?php

namespace App\Models;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Modules\Product\Models\Product;
use App\Models\InsuranceAgency;
use App\Models\InsuranceContract;
class RevenueDayly extends Model
{
    protected $table = 'revenue_dayly';
    protected $fillable = [
        'insurance_agency_id','self_revenue', 'branch_revenue','self_income', 'branch_income', 'month','day', 'date','year', 'nolife_before_tax','nolife_affter_tax','life_before_tax','life_affter_tax'
    ];
    public function insurance_agency()
    {
        return $this->belongsTo('App\Models\InsuranceAgency');
    }
    public static function plugIncomeAgencyFirtRevenueMonthlyConvertDay($month,$contract_id,$self_commission,$maxmoney = 50000000){


        $data_contract = InsuranceContract::where('id',$contract_id)->first();
        $date = date('Y-m-d', strtotime($data_contract->date_signed));
        $year = date('Y', strtotime($data_contract->date_signed));
        $month = date('m', strtotime($data_contract->date_signed));
        $day = date('d', strtotime($data_contract->date_signed));
        $check_product = Product::where('id',$data_contract->product_id)->first();
        $check_agency =  InsuranceAgency::where('id' , $data_contract->sale_type_id)->first();
        $data_reve  =RevenueDayly::where([['insurance_agency_id',$data_contract->sale_type_id],['month','=', $month],['day','=',$day],['year','=',$year]])->first();
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
                        'day' => $day,
                        'date' =>$date
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
                        'self_revenue'=>($check_product->is_agency == 1) ? $data_contract->require_pay_amount:0,
                        'life_before_tax'=>round($data_contract->require_pay_amount*$check_product->PEYP*$self_commission/100,0),
                        'life_affter_tax'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100,0),
                        'self_income'=> round($data_contract->require_pay_amount*$check_product->PEYP*0.9*$self_commission/100,0 ),
                        'month' => $month,
                        'year' => $year,
                        'day' => $day,
                        'date' =>$date
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
                    'day' => $day,
                    'date' =>$date
                ]);
            }
        }
        return true;
    }

    public static  function plusAgencyParentRevenuesConvertDay($month, $contract,$agency,$i){
        $product = Product::where('id',$contract->product_id)->first();
        $date = date('Y-m-d', strtotime($contract->date_signed));
        $year = date('Y', strtotime($contract->date_signed));
        $month = date('m', strtotime($contract->date_signed));
        $day = date('d', strtotime($contract->date_signed));
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
            $revenue_dayly = RevenueDayly::where([ ['insurance_agency_id','=', $agency->parent_id], ['month','=',$month],['day' ,'=',$day], ['year','=', date('Y')]])->first();
            if($revenue_dayly != null){
                $chec = $revenue_dayly->update([
                    'branch_revenue' => (int)$revenue_dayly->branch_revenue + (int)$contract->require_pay_amount,
                    'branch_income' => (int)($revenue_dayly->branch_income) + (int)(($money_affter_tax['self_income'])?$money_affter_tax['self_income']:0),
                    'nolife_before_tax' => (int)$revenue_dayly ->nolife_before_tax + (int)($money_affter_tax['nolife_before_tax'])?$money_affter_tax['nolife_before_tax']:0,
                    'nolife_affter_tax' => (int)$revenue_dayly ->nolife_affter_tax + (int)($money_affter_tax['nolife_affter_tax'])?$money_affter_tax['nolife_affter_tax']:0,
                    'life_before_tax' => (int)$revenue_dayly ->life_before_tax + (int)($money_affter_tax['life_before_tax'])?$money_affter_tax['life_before_tax']:0,
                    'life_affter_tax' => (int)$revenue_dayly ->life_affter_tax + (int)($money_affter_tax['life_affter_tax'])?$money_affter_tax['life_affter_tax']:0,
                ]);
                if($chec != true){
                    dd($contract->id);
                }
            }else{
                $check2 = RevenueDayly::create([
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
                    'year' => $year,
                    'day' =>$day,
                    'date' =>$date

                ]);
                if($check2 == null){
                    dd($contract->id);
                }
            }
            if($i < 100 && $parent__agency->parent_id != 0){
                $i++;
                RevenueDayly::plusAgencyParentRevenuesConvertDay($month,$contract,$parent__agency,$i);
            }
        }
    }
    
}
