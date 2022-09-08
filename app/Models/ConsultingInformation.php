<?php

namespace App\Models;
use App\Models\InsuranceAgency;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;

class ConsultingInformation extends Model
{
    protected $table = 'consulting_information';
    protected $fillable = [
        'email',
        'customer_id',
        'name_customer',
        'phone_number',
        'date_of_birth',
        'insurance_clain_number',
        'address',
        'insurance_agency_id',
        'fee_payment_date',
        'main_product_id',
        'amount_paid',
        'quantity_product_sup',
        'total_amount_sup',
        'id_card_number',
        'img_url',
        'img_info_url',
        'sex',
        'agency_info_fwd_id',
        'province_id',
        'date_card_number',
        'place_card_number',
        'contract_number',
        'status_send_open_letter',
        'pdf_open_letter',
        'insurance_company_id',
        'p_fyp_temporary',
        'periodic_fee_type',
        'renewals_customer_status',
        'payment_via',
        'effective_date',
        'gross_amount',
        'vat',
        'net_amount',
        'approve_status'
    ];
    
    public function insurance_agency()
    {
        return $this->belongsTo('App\Models\InsuranceAgency', 'insurance_agency_id');
    }

    public function product()
    {
        return $this->belongsTo('Modules\Product\Models\Product', 'main_product_id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer', 'customer_id');
    }

    public function agencyInfoFwd()
    {
        return $this->belongsTo('App\Models\AgencyInfoFwd', 'agency_info_fwd_id');
    }

    public function insuranceCompany()
    {
        return $this->belongsTo('App\Models\InsuranceCompany', 'insurance_company_id');
    }
    
    public static function searchIndex($params, $agencyId = null) {

        $startDefault = Carbon::now()->startOfMonth()->format('d/m/Y');
        $endDefault = Carbon::now()->endOfMonth()->format('d/m/Y');
        $submit = !empty($params['submit']) ? $params['submit'] : '';
        $date_type = !empty($params['date_type']) ? $params['date_type'] : 0;
        $start = !empty($params['start']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["start"]))).' 00:00:00' : date("Y-m-d",strtotime(str_replace('/', '-', $startDefault))).' 00:00:00';
        $end = !empty($params['end']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["end"]))).' 23:59:59' : date("Y-m-d",strtotime(str_replace('/', '-', $endDefault))).' 23:59:59';

        $list = ConsultingInformation::select(
            'consulting_information.*', 
            'c.email as email', 
            'c.name as name', 
            'c.phone_number as phone_number',
            'insurance_contracts.pass_ack_date',
            'insurance_contracts.effective_date',
            'insurance_contracts.status',
            'insurance_contracts.fee_payment_date',
            'insurance_contracts.salary_payment_status',
            'insurance_contracts.revenue_cycle',
            'insurance_contracts.contract_submission_date',
            'insurance_contracts.p_fyp',
            'consulting_information.created_at as consulting_create'
        )
            ->leftjoin('customers as c', 'c.id', 'consulting_information.customer_id')
            ->leftjoin('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
            ->leftjoin('insurance_contracts', 'insurance_contracts.consulting_information_id', 'consulting_information.id')
            ->leftjoin('mp_products as p', 'p.id', 'consulting_information.main_product_id')
            ->whereIn('p.insurance_company_id', [1])
            ->whereIn('consulting_information.insurance_company_id', [1]);
        if(!empty($agencyId)){
            $list = $list->where('consulting_information.insurance_agency_id', $agencyId);
        }
        if (!empty($params["name_customer"])) {
            $list = $list->where('c.name', 'like', '%'.$params["name_customer"].'%');
        }
        if (!empty($params["name"])) {

            $list = $list->where('i.name', 'like', '%'.$params["name"].'%');
        }
        if (!empty($params["revenue_cycle"])) {
            $list = $list->whereIn('insurance_contracts.revenue_cycle', $params["revenue_cycle"]);
        }
        if (!empty($params["code_agency"])) {
            $list = $list->where('i.code_agency', $params["code_agency"]);
        }

        if (!empty($params["phone"])) {
            $list = $list->where('c.phone_number', $params["phone"]);
        }
        if($date_type == 1){
            //Ngày nộp hợp đồng 
            $list = $list->whereBetween('contract_submission_date', [$start, $end]);
        }else if($date_type == 2){
            //Ngày ACK + 21
            $list = $list->whereBetween('pass_ack_date', [$start, $end]);
        }else if($date_type == 3){
            //Ngày nhập báo cáo
            $list = $list->whereBetween('consulting_information.created_at', [$start, $end]);
        }  
        if (!empty($params["contract_number"])) {
            $list = $list->where('consulting_information.contract_number', $params["contract_number"]);
        }
        $list = $list->orderBy('insurance_contracts.ack_date','DESC')->orderBy('insurance_contracts.effective_date','DESC')->orderBy('insurance_contracts.status','ASC')->orderBy('insurance_contracts.fee_payment_date','DESC');
        return $list;
    }
    public static function searchSumP_fyp($params, $agencyId = null) {

        $startDefault = Carbon::now()->startOfMonth()->format('d/m/Y');
        $endDefault = Carbon::now()->endOfMonth()->format('d/m/Y');
        $submit = !empty($params['submit']) ? $params['submit'] : '';
        $date_type = !empty($params['date_type']) ? $params['date_type'] : 0;
        $start = !empty($params['start']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["start"]))).' 00:00:00' : date("Y-m-d",strtotime(str_replace('/', '-', $startDefault))).' 00:00:00';
        $end = !empty($params['end']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["end"]))).' 23:59:59' : date("Y-m-d",strtotime(str_replace('/', '-', $endDefault))).' 23:59:59';

        $list = ConsultingInformation::select('insurance_contracts.p_fyp')
                    ->leftjoin('customers as c', 'c.id', 'consulting_information.customer_id')
                    ->leftjoin('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
                    ->leftjoin('insurance_contracts', 'insurance_contracts.consulting_information_id', 'consulting_information.id')
                    ->leftjoin('mp_products as p', 'p.id', 'consulting_information.main_product_id')
                    ->whereNotIn('insurance_contracts.status', ['FL','LS','NT','SU','WD'])
                    ->where('p.insurance_company_id',1);
        if(!empty($agencyId)){
            $list = $list->where('consulting_information.insurance_agency_id', $agencyId);
        }
        if (!empty($params["name_customer"])) {
            $list = $list->where('c.name', 'like', '%'.$params["name_customer"].'%');
        }
        if (!empty($params["name"])) {

            $list = $list->where('i.name', 'like', '%'.$params["name"].'%');
        }
        if (!empty($params["revenue_cycle"])) {
            $list = $list->whereIn('insurance_contracts.revenue_cycle', $params["revenue_cycle"]);
        }
        if (!empty($params["code_agency"])) {
            $list = $list->where('i.code_agency', $params["code_agency"]);
        }

        if (!empty($params["phone"])) {
            $list = $list->where('c.phone_number', $params["phone"]);
        }
        if($date_type == 1){
            //Ngày nộp hợp đồng 
            $list = $list->whereBetween('contract_submission_date', [$start, $end]);
        }else if($date_type == 2){
            //Ngày ACK + 21
            $list = $list->whereBetween('pass_ack_date', [$start, $end]);
        }else if($date_type == 3){
            //Ngày nhập báo cáo
            $list = $list->whereBetween('consulting_information.created_at', [$start, $end]);
        } 
        if (!empty($params["contract_number"])) {
            $list = $list->where('consulting_information.contract_number', $params["contract_number"]);
        }
        return $list->sum('p_fyp');
    }

    public static function searchExcelIndex($params, $agencyId = null) {
        $startDefault = Carbon::now()->startOfMonth()->format('d/m/Y');
        $endDefault = Carbon::now()->endOfMonth()->format('d/m/Y');
        $submit = !empty($params['submit']) ? $params['submit'] : '';
        $date_type = !empty($params['date_type']) ? $params['date_type'] : 0;
        $start = !empty($params['start']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["start"]))).' 00:00:00' : date("Y-m-d",strtotime(str_replace('/', '-', $startDefault))).' 00:00:00';
        $end = !empty($params['end']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["end"]))).' 23:59:59' : date("Y-m-d",strtotime(str_replace('/', '-', $endDefault))).' 23:59:59';
        
        $list = ConsultingInformation::select('consulting_information.*', 'c.email as email', 'c.name as name', 'c.phone_number as phone_number',
        'insurance_contracts.pass_ack_date','insurance_contracts.effective_date','insurance_contracts.status','insurance_contracts.fee_payment_date','insurance_contracts.salary_payment_status','insurance_contracts.revenue_cycle','insurance_contracts.contract_submission_date','insurance_contracts.p_fyp')
                    ->leftjoin('customers as c', 'c.id', 'consulting_information.customer_id')
                    ->leftjoin('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
                    ->leftjoin('insurance_contracts', 'insurance_contracts.consulting_information_id', 'consulting_information.id')
                    ->leftjoin('mp_products as p', 'p.id', 'consulting_information.main_product_id')
                    ->where('p.insurance_company_id',1);
                    if(!empty($agencyId)){
                        $list = $list->where('consulting_information.insurance_agency_id', $agencyId);
                    }
                    if (!empty($params["name_customer"])) {
                        $list = $list->where('c.name', 'like', '%'.$params["name_customer"].'%');
                    }
                    if (!empty($params["name"])) {
            
                        $list = $list->where('i.name', 'like', '%'.$params["name"].'%');
                    }
                    if (!empty($params["revenue_cycle"])) {
                        $list = $list->whereIn('insurance_contracts.revenue_cycle', $params["revenue_cycle"]);
                    }
                    if (!empty($params["code_agency"])) {
                        $list = $list->where('i.code_agency', $params["code_agency"]);
                    }
            
                    if (!empty($params["phone"])) {
                        $list = $list->where('c.phone_number', $params["phone"]);
                    }
                    if($date_type == 1){
                        //Ngày nộp hợp đồng 
                        $list = $list->whereBetween('contract_submission_date', [$start, $end]);
                    }else if($date_type == 2){
                        //Ngày ACK + 21
                        $list = $list->whereBetween('pass_ack_date', [$start, $end]);
                    }else if($date_type == 3){
                            //Ngày nhập báo cáo
                        $list = $list->whereBetween('consulting_information.created_at', [$start, $end]);
                    } 
                    if (!empty($params["contract_number"])) {
                        $list = $list->where('consulting_information.contract_number', $params["contract_number"]);
                    }
        $list = $list->orderBy('insurance_contracts.ack_date','DESC')->orderBy('insurance_contracts.effective_date','DESC')->orderBy('insurance_contracts.status','ASC')->orderBy('insurance_contracts.fee_payment_date','DESC');

        return $list->get();
    }

    public static function searchF1Index($params, $parent_check_id= null, $id=null,$agencyId = null) {
        $check = !empty($params['type_insurance']) ? $params['type_insurance'] : 0;
        if($check == 1){
            $type_insurance = [1];
        }elseif($check == 0){
            $type_insurance = [1,2,3];
        }else{
            $type_insurance = [2,3];
        }
        $startDefault = Carbon::now()->startOfMonth()->format('d/m/Y');
        $endDefault = Carbon::now()->endOfMonth()->format('d/m/Y');
        $submit = !empty($params['submit']) ? $params['submit'] : '';
        $date_type = !empty($params['date_type']) ? $params['date_type'] : 0;
        $start = !empty($params['start']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["start"]))).' 00:00:00' : date("Y-m-d",strtotime(str_replace('/', '-', $startDefault))).' 00:00:00';
        $end = !empty($params['end']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["end"]))).' 23:59:59' : date("Y-m-d",strtotime(str_replace('/', '-', $endDefault))).' 23:59:59';
       
        $user = User::where('group_id', 3)->find($params['user_id']);
        if($user->group_id != config('core.group_id.agency')) {
            if(empty($params['code_agency'])){
                $agencyList = InsuranceAgency::get()->pluck('id')->toArray();
            }else{
                $agencycheck = InsuranceAgency::where('code_agency',$params['code_agency'])->first();
                $parentId = [$agencycheck->id];
                $agencyTree = [$agencycheck->id];                        
                while (count($parentId) > 0){
                    $childs = InsuranceAgency::selectRaw("insurance_agencies.*")
                                        ->whereIn('insurance_agencies.parent_id', $parentId)
                                        ->whereNotNull('insurance_agencies.code_agency')
                                        ->where('users.status','<>', 3)
                                        ->leftjoin('users', 'users.id', 'insurance_agencies.user_id')
                                        ->get();
                        $parentId = [];
                        if(!empty($childs)){
                            foreach($childs as $child){
                                array_push($agencyTree, $child->id);
                                array_push($parentId, $child->id);
                        }
                    }
                }
                $agencyList = InsuranceAgency::whereIn('id', $agencyTree)->get()->pluck('id')->toArray(); 
            }
        }else {
            if(empty($params['code_agency'])){
                $insurance_agency = $user->insuranceAgency;
                $parentId = [$insurance_agency->id];
                $agencyTree = [$insurance_agency->id];                        
                while (count($parentId) > 0){
                    $childs = InsuranceAgency::selectRaw("insurance_agencies.*")
                                        ->whereIn('insurance_agencies.parent_id', $parentId)
                                        ->whereNotNull('insurance_agencies.code_agency')
                                        ->where('users.status','<>', 3)
                                        ->leftjoin('users', 'users.id', 'insurance_agencies.user_id')
                                        ->get();
                        $parentId = [];
                        if(!empty($childs)){
                            foreach($childs as $child){
                                array_push($agencyTree, $child->id);
                                array_push($parentId, $child->id);
                        }
                    }
                }
                $agencyList = InsuranceAgency::whereIn('id', $agencyTree)->get()->pluck('id')->toArray(); 
            }else{
                $agencycheck = InsuranceAgency::where('code_agency',$params['code_agency'])->first();
                $parentId = [$agencycheck->id];
                $agencyTree = [$agencycheck->id];                        
                while (count($parentId) > 0){
                    $childs = InsuranceAgency::selectRaw("insurance_agencies.*")
                                        ->whereIn('insurance_agencies.parent_id', $parentId)
                                        ->whereNotNull('insurance_agencies.code_agency')
                                        ->where('users.status','<>', 3)
                                        ->leftjoin('users', 'users.id', 'insurance_agencies.user_id')
                                        ->get();
                        $parentId = [];
                        if(!empty($childs)){
                            foreach($childs as $child){
                                array_push($agencyTree, $child->id);
                                array_push($parentId, $child->id);
                        }
                    }
                }
                $agencyList = InsuranceAgency::whereIn('id', $agencyTree)->get()->pluck('id')->toArray(); 
            }
            
        }
            $list = ConsultingInformation::select('consulting_information.*', 'c.email as email', 'c.name as name', 'c.phone_number as phone_number','i.parent_id','i.level_id',
            'insurance_contracts.pass_ack_date','insurance_contracts.effective_date','insurance_contracts.status','insurance_contracts.fee_payment_date','insurance_contracts.salary_payment_status','insurance_contracts.revenue_cycle','insurance_contracts.contract_submission_date','insurance_contracts.p_fyp','consulting_information.created_at as c_created_at')
            ->leftjoin('customers as c', 'c.id', 'consulting_information.customer_id')
            ->leftjoin('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
            ->leftjoin('insurance_contracts', 'insurance_contracts.consulting_information_id', 'consulting_information.id')
            ->leftjoin('mp_products as p', 'p.id', 'consulting_information.main_product_id')
            ->whereIn('p.insurance_company_id',[1,10])
            ->whereIn('consulting_information.insurance_agency_id', $agencyList);
            if($user->group_id != config('core.group_id.agency')) {

                if(empty($params['code_agency'])){
                    $list = $list->where('consulting_information.insurance_agency_id' ,'!=',1);
                }else{
                    $agency = InsuranceAgency::where('code_agency',$params['code_agency'])->first();
                    $list = $list->where('consulting_information.insurance_agency_id' ,'!=',$agency->id);
                }
               
            }else {
                $list = $list->where('consulting_information.insurance_agency_id' ,'!=',$user->insuranceAgency->id);
            }
            if (!empty($params["name_customer"])) {
                $list = $list->where('c.name', 'like', '%'.$params["name_customer"].'%');
            }
            if (!empty($params["name"])) {
        
                $list = $list->where('i.name', 'like', '%'.$params["name"].'%');
            }
            if (!empty($params["revenue_cycle"])) {
                $list = $list->whereIn('insurance_contracts.revenue_cycle', $params["revenue_cycle"]);
            }
            if (!empty($params["phone"])) {
                $list = $list->where('c.phone_number', $params["phone"]);
            }
            if (!empty($params["contract_number"])) {
                $list = $list->where('consulting_information.contract_number', $params["contract_number"]);
            }
            if($date_type == 1){
                //Ngày nộp hợp đồng 
                $list = $list->whereBetween('contract_submission_date', [$start, $end]);
            }else if($date_type == 2){
                //Ngày ACK + 21
                $list = $list->whereBetween('pass_ack_date', [$start, $end]);
            }else if($date_type == 0){
                $list = $list->whereBetween('consulting_information.created_at', [$start, $end]);
            }else if($date_type == 3){
                //Ngày nhập báo cáo
                $list = $list->whereBetween('consulting_information.created_at', [$start, $end]);
            } 
        $listData = $list->orderBy('consulting_information.created_at','DESC')->orderBy('insurance_contracts.pass_ack_date','DESC')->orderBy('insurance_contracts.effective_date','DESC')->orderBy('status','ASC');  
        return $listData ;      
    }
    public static function searchF1SumP_fyp($params, $parent_check_id= null, $id=null,$agencyId = null) {
        $check = !empty($params['type_insurance']) ? $params['type_insurance'] : 0;
        if($check == 1){
            $type_insurance = [1];
        }elseif($check == 0){
            $type_insurance = [1,2,3];
        }else{
            $type_insurance = [2,3];
        }
        $startDefault = Carbon::now()->startOfMonth()->format('d/m/Y');
        $endDefault = Carbon::now()->endOfMonth()->format('d/m/Y');
        $submit = !empty($params['submit']) ? $params['submit'] : '';
        $date_type = !empty($params['date_type']) ? $params['date_type'] : 0;
        $start = !empty($params['start']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["start"]))).' 00:00:00' : date("Y-m-d",strtotime(str_replace('/', '-', $startDefault))).' 00:00:00';
        $end = !empty($params['end']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["end"]))).' 23:59:59' : date("Y-m-d",strtotime(str_replace('/', '-', $endDefault))).' 23:59:59';
       
        $user = User::where('group_id', 3)->find($params['user_id']);
        if($user->group_id != config('core.group_id.agency')) {
            if(empty($params['code_agency'])){
                $agencyList = InsuranceAgency::get()->pluck('id')->toArray();
            }else{
                $agencycheck = InsuranceAgency::where('code_agency',$params['code_agency'])->first();
                $parentId = [$agencycheck->id];
                $agencyTree = [$agencycheck->id];                        
                while (count($parentId) > 0){
                    $childs = InsuranceAgency::selectRaw("insurance_agencies.*")
                                        ->whereIn('insurance_agencies.parent_id', $parentId)
                                        ->whereNotNull('insurance_agencies.code_agency')
                                        ->where('users.status','<>', 3)
                                        ->leftjoin('users', 'users.id', 'insurance_agencies.user_id')
                                        ->get();
                        $parentId = [];
                        if(!empty($childs)){
                            foreach($childs as $child){
                                array_push($agencyTree, $child->id);
                                array_push($parentId, $child->id);
                        }
                    }
                }
                $agencyList = InsuranceAgency::whereIn('id', $agencyTree)->get()->pluck('id')->toArray(); 
            }
        }else {
            if(empty($params['code_agency'])){
                $insurance_agency = $user->insuranceAgency;
                $parentId = [$insurance_agency->id];
                $agencyTree = [$insurance_agency->id];                        
                while (count($parentId) > 0){
                    $childs = InsuranceAgency::selectRaw("insurance_agencies.*")
                                        ->whereIn('insurance_agencies.parent_id', $parentId)
                                        ->whereNotNull('insurance_agencies.code_agency')
                                        ->where('users.status','<>', 3)
                                        ->leftjoin('users', 'users.id', 'insurance_agencies.user_id')
                                        ->get();
                        $parentId = [];
                        if(!empty($childs)){
                            foreach($childs as $child){
                                array_push($agencyTree, $child->id);
                                array_push($parentId, $child->id);
                        }
                    }
                }
                $agencyList = InsuranceAgency::whereIn('id', $agencyTree)->get()->pluck('id')->toArray(); 
            }else{
                $agencycheck = InsuranceAgency::where('code_agency',$params['code_agency'])->first();
                $parentId = [$agencycheck->id];
                $agencyTree = [$agencycheck->id];                        
                while (count($parentId) > 0){
                    $childs = InsuranceAgency::selectRaw("insurance_agencies.*")
                                        ->whereIn('insurance_agencies.parent_id', $parentId)
                                        ->whereNotNull('insurance_agencies.code_agency')
                                        ->where('users.status','<>', 3)
                                        ->leftjoin('users', 'users.id', 'insurance_agencies.user_id')
                                        ->get();
                        $parentId = [];
                        if(!empty($childs)){
                            foreach($childs as $child){
                                array_push($agencyTree, $child->id);
                                array_push($parentId, $child->id);
                        }
                    }
                }
                $agencyList = InsuranceAgency::whereIn('id', $agencyTree)->get()->pluck('id')->toArray(); 
            }
            
        }
            $list = ConsultingInformation::select('insurance_contracts.p_fyp')
            ->leftjoin('customers as c', 'c.id', 'consulting_information.customer_id')
            ->leftjoin('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
            ->leftjoin('insurance_contracts', 'insurance_contracts.consulting_information_id', 'consulting_information.id')
            ->leftjoin('mp_products as p', 'p.id', 'consulting_information.main_product_id')
            ->whereIn('p.insurance_company_id',[1])
            ->whereIn('consulting_information.insurance_agency_id', $agencyList)
            ->whereNotIn('insurance_contracts.status', ['FL','LS','NT','SU','WD']);
            if($user->group_id != config('core.group_id.agency')) {

                if(empty($params['code_agency'])){
                    $list = $list->where('consulting_information.insurance_agency_id' ,'!=',1);
                }else{
                    $agency = InsuranceAgency::where('code_agency',$params['code_agency'])->first();
                    $list = $list->where('consulting_information.insurance_agency_id' ,'!=',$agency->id);
                }
               
            }else {
                $list = $list->where('consulting_information.insurance_agency_id' ,'!=',$user->insuranceAgency->id);
            }
            if (!empty($params["name_customer"])) {
                $list = $list->where('c.name', 'like', '%'.$params["name_customer"].'%');
            }
            if (!empty($params["name"])) {
        
                $list = $list->where('i.name', 'like', '%'.$params["name"].'%');
            }
            if (!empty($params["revenue_cycle"])) {
                $list = $list->whereIn('insurance_contracts.revenue_cycle', $params["revenue_cycle"]);
            }
            if (!empty($params["phone"])) {
                $list = $list->where('c.phone_number', $params["phone"]);
            }
            if (!empty($params["contract_number"])) {
                $list = $list->where('consulting_information.contract_number', $params["contract_number"]);
            }
            if($date_type == 1){
                //Ngày nộp hợp đồng 
                $list = $list->whereBetween('contract_submission_date', [$start, $end]);
            }else if($date_type == 2){
                //Ngày ACK + 21
                $list = $list->whereBetween('pass_ack_date', [$start, $end]);
            }else if($date_type == 0){
                $list = $list->whereBetween('consulting_information.created_at', [$start, $end]);
            }else if($date_type == 3){
                //Ngày nhập báo cáo
                $list = $list->whereBetween('consulting_information.created_at', [$start, $end]);
            } 
        return $listData = $list->sum('p_fyp') ;      
    }

    public static function searchF1IndexClick($params, $parent_check_id= null, $id=null,$agencyId = null) {
        $check = !empty($params['type_insurance']) ? $params['type_insurance'] : 0;
        if($check == 1){
            $type_insurance = [1];
        }elseif($check == 0){
            $type_insurance = [1,2,3];
        }else{
            $type_insurance = [2,3];
        }
        $startDefault = Carbon::now()->startOfMonth()->format('d/m/Y');
        $endDefault = Carbon::now()->endOfMonth()->format('d/m/Y');
        $submit = !empty($params['submit']) ? $params['submit'] : '';
        $date_type = !empty($params['date_type']) ? $params['date_type'] : 0;
        $start = !empty($params['start']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["start"]))).' 00:00:00' : date("Y-m-d",strtotime(str_replace('/', '-', $startDefault))).' 00:00:00';
        $end = !empty($params['end']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["end"]))).' 23:59:59' : date("Y-m-d",strtotime(str_replace('/', '-', $endDefault))).' 23:59:59';
       
            $user = Auth::user();
            $insurance_agency = $user->insurance_agency;
            $parentId = [$parent_check_id];
            $agencyTree = [$parent_check_id];                        
            while (count($parentId) > 0){
                $childs = InsuranceAgency::selectRaw("insurance_agencies.*")
                                    ->whereIn('insurance_agencies.parent_id', $parentId)
                                    ->whereNotNull('insurance_agencies.code_agency')
                                    ->where('users.status','<>', 3)
                                    ->leftjoin('users', 'users.id', 'insurance_agencies.user_id')
                                    ->get();
                    $parentId = [];
                    if(!empty($childs)){
                        foreach($childs as $child){
                            array_push($agencyTree, $child->id);
                            array_push($parentId, $child->id);
                    }
                }
            }
            
            $agencyList = InsuranceAgency::whereIn('id', $agencyTree)->get()->pluck('id')->toArray(); 
            $list = ConsultingInformation::select('consulting_information.*', 'c.email as email', 'c.name as name', 'c.phone_number as phone_number','i.parent_id','i.level_id',
            'insurance_contracts.pass_ack_date','insurance_contracts.effective_date','insurance_contracts.status','insurance_contracts.fee_payment_date','insurance_contracts.salary_payment_status','insurance_contracts.revenue_cycle','insurance_contracts.contract_submission_date','insurance_contracts.p_fyp')
            ->leftjoin('customers as c', 'c.id', 'consulting_information.customer_id')
            ->leftjoin('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
            ->leftjoin('insurance_contracts', 'insurance_contracts.consulting_information_id', 'consulting_information.id')
            ->leftjoin('mp_products as p', 'p.id', 'consulting_information.main_product_id')
            ->whereIn('p.insurance_company_id',$type_insurance)
            ->whereIn('consulting_information.insurance_agency_id', $agencyList);
            if($user->group_id != config('core.group_id.agency')) {
                $list = $list->where('consulting_information.insurance_agency_id' ,'!=',$parent_check_id);
            }else {
                $list = $list->where('consulting_information.insurance_agency_id' ,'!=',$insurance_agency->id);
            }
            if (!empty($params["name_customer"])) {
                $list = $list->where('c.name', 'like', '%'.$params["name_customer"].'%');
            }
            if (!empty($params["name"])) {
        
                $list = $list->where('i.name', 'like', '%'.$params["name"].'%');
            }
            if (!empty($params["revenue_cycle"])) {
                $list = $list->whereIn('insurance_contracts.revenue_cycle', $params["revenue_cycle"]);
            }
            if (!empty($params["email"])) {
                $list = $list->where('c.email', $params["email"]);
            }
        
            if (!empty($params["phone"])) {
                $list = $list->where('c.phone_number', $params["phone"]);
            }
            if (!empty($params["contract_number"])) {
                $list = $list->where('consulting_information.contract_number', $params["contract_number"]);
            }
            if($date_type == 1){
                //Ngày nộp hợp đồng 
                $list = $list->whereBetween('contract_submission_date', [$start, $end]);
            }else if($date_type == 2){
                //Ngày ACK + 21
                $list = $list->whereBetween('pass_ack_date', [$start, $end]);
            }
        $listData = $list->orderBy('insurance_contracts.pass_ack_date','DESC')->orderBy('insurance_contracts.effective_date','DESC')->orderBy('status','ASC')->paginate(50);  
        return $listData ;      
    }

    public static function searchNonLifeIndex($params, $agencyId = null) {
        $date_type = !empty($params['date_type']) ? $params['date_type'] : 0;
        $start = !empty($params['start']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["start"]))).' 00:00:00' : date("Y-m-d",strtotime(str_replace('/', '-', Carbon::now()->startOfMonth()->format('d/m/Y'))));
        $end = !empty($params['end']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["end"]))).' 23:59:59' : date("Y-m-d",strtotime(str_replace('/', '-', Carbon::now()->endOfMonth()->format('d/m/Y'))));
        $list = ConsultingInformation::select(
                        'consulting_information.*', 
                        'c.email as email', 
                        'c.name as name', 
                        'c.phone_number as phone_number',
                        'contract.discount_amount'
                    )
                    ->leftjoin('customers as c', 'c.id', 'consulting_information.customer_id')
                    ->leftjoin('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
                    ->leftjoin('insurance_contracts as contract', 'contract.consulting_information_id', 'consulting_information.id')
                    ->join('mp_products as p', 'p.id', 'consulting_information.main_product_id')
                    ->whereNotIn('p.insurance_company_id',[1]);
                    
        if(!empty($agencyId)){
            $list = $list->where('consulting_information.insurance_agency_id', $agencyId);
        }
        if (!empty($params["name_customer"])) {
            $list = $list->where('c.name', 'like', '%'.$params["name_customer"].'%');
        }
        if (!empty($params["name"])) {

            $list = $list->where('i.name', 'like', '%'.$params["name"].'%');
        }
        if (!empty($params["code_agency"])) {

            $list = $list->where('i.code_agency', 'like', '%'.$params["code_agency"].'%');
        }

        if (!empty($params["phone"])) {
            $list = $list->where('c.phone_number', $params["phone"]);
        }

        if (!empty($params["contract_number"])) {
            $list = $list->where('consulting_information.contract_number', $params["contract_number"]);
        }

        if (!empty($params["company_id"])) {
            $list = $list->where('consulting_information.insurance_company_id', $params["company_id"]);
        }

        if($date_type == 1){
            $list = $list->whereBetween('consulting_information.effective_date', [$start, $end]);
        }else if($date_type == 2){
            $list = $list->whereBetween('consulting_information.fee_payment_date', [$start, $end]);
        }
        $list = $list->orderBy('consulting_information.fee_payment_date','DESC');
        return $list->paginate(50);
    }

    public static function searchF1NonLifeIndex($params, $parent_id= null, $id=null,$agencyId = null) {
        $list = ConsultingInformation::select('consulting_information.*', 'c.email as email', 'c.name as name', 'c.phone_number as phone_number','i.parent_id','i.level_id')
                    ->join('customers as c', 'c.id', 'consulting_information.customer_id')
                    ->join('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
                    ->leftjoin('insurance_contracts', 'insurance_contracts.consulting_information_id', 'consulting_information.id')
                    ->join('mp_products as p', 'p.id', 'consulting_information.main_product_id')
                    ->whereNotIn('p.insurance_company_id',[1]);

        $listfor = $list->where('i.parent_id', $parent_id)->get();
        $list = $list->where('i.parent_id', $parent_id);

        foreach ($listfor as $key => $item){
            $child = ConsultingInformation::select('consulting_information.*',
            'c.email as email', 'c.name as name', 'c.phone_number as phone_number','i.parent_id','i.level_id')
            ->leftjoin('customers as c', 'c.id', 'consulting_information.customer_id')
            ->leftjoin('insurance_contracts', 'insurance_contracts.consulting_information_id', 'consulting_information.id')
            ->leftjoin('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
            ->where('i.parent_id', $item->insurance_agency_id)
            ->join('mp_products as p', 'p.id', 'consulting_information.main_product_id')
            ->whereNotIn('p.insurance_company_id',[1]);
            $child1 =$child->get();
            $list = $list->union($child);
            foreach ($child1 as $key => $i){
                $child2 = ConsultingInformation::select('consulting_information.*',
                'c.email as email', 'c.name as name', 'c.phone_number as phone_number','i.parent_id','i.level_id')
                ->leftjoin('customers as c', 'c.id', 'consulting_information.customer_id')
                ->leftjoin('insurance_contracts', 'insurance_contracts.consulting_information_id', 'consulting_information.id')
                ->leftjoin('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
                ->where('i.parent_id', $i->insurance_agency_id)
                ->join('mp_products as p', 'p.id', 'consulting_information.main_product_id')
                ->whereNotIn('p.insurance_company_id',[1]);
                $child2_for= $child2->get();
                $list = $list->union($child2);
                foreach ($child2_for as $key => $k){
                    $child3 = ConsultingInformation::select('consulting_information.*',
                    'c.email as email', 'c.name as name', 'c.phone_number as phone_number','i.parent_id','i.level_id')
                    ->leftjoin('customers as c', 'c.id', 'consulting_information.customer_id')
                    ->leftjoin('insurance_contracts', 'insurance_contracts.consulting_information_id', 'consulting_information.id')
                    ->leftjoin('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
                    ->where('i.parent_id', $k->insurance_agency_id)
                    ->join('mp_products as p', 'p.id', 'consulting_information.main_product_id')
                    ->whereNotIn('p.insurance_company_id',[1]);
                    $child3_for= $child3->get();
                    $list = $list->union($child3);
                    foreach ($child3_for as $key => $l){
                        $child4 = ConsultingInformation::select('consulting_information.*',
                        'c.email as email', 'c.name as name', 'c.phone_number as phone_number','i.parent_id','i.level_id')
                        ->leftjoin('customers as c', 'c.id', 'consulting_information.customer_id')
                        ->leftjoin('insurance_contracts', 'insurance_contracts.consulting_information_id', 'consulting_information.id')
                        ->leftjoin('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
                        ->where('i.parent_id', $l->insurance_agency_id)
                        ->join('mp_products as p', 'p.id', 'consulting_information.main_product_id')
                        ->whereNotIn('p.insurance_company_id',[1]);
                        $child4_for= $child4->get();
                        $list = $list->union($child4);
                        foreach ($child4_for as $key => $m){
                            $child5 = ConsultingInformation::select('consulting_information.*',
                            'c.email as email', 'c.name as name', 'c.phone_number as phone_number','i.parent_id','i.level_id')
                            ->leftjoin('customers as c', 'c.id', 'consulting_information.customer_id')
                            ->leftjoin('insurance_contracts', 'insurance_contracts.consulting_information_id', 'consulting_information.id')
                            ->leftjoin('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
                            ->where('i.parent_id', $m->insurance_agency_id)
                            ->join('mp_products as p', 'p.id', 'consulting_information.main_product_id')
                            ->whereNotIn('p.insurance_company_id',[1]);
                            $child5_for= $child5->get();
                            $list = $list->union($child5);
                            foreach ($child5_for as $key => $n){
                                $child6 = ConsultingInformation::select('consulting_information.*',
                                'c.email as email', 'c.name as name', 'c.phone_number as phone_number','i.parent_id','i.level_id')
                                ->leftjoin('customers as c', 'c.id', 'consulting_information.customer_id')
                                ->leftjoin('insurance_contracts', 'insurance_contracts.consulting_information_id', 'consulting_information.id')
                                ->leftjoin('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
                                ->where('i.parent_id', $n->insurance_agency_id)
                                ->join('mp_products as p', 'p.id', 'consulting_information.main_product_id')
                                ->whereNotIn('p.insurance_company_id',[1]);
                                $child6_for= $child6->get();
                                $list = $list->union($child6);
                                foreach ($child6_for as $key => $o){
                                    $child7 = ConsultingInformation::select('consulting_information.*',
                                    'c.email as email', 'c.name as name', 'c.phone_number as phone_number','i.parent_id','i.level_id')
                                    ->leftjoin('customers as c', 'c.id', 'consulting_information.customer_id')
                                    ->leftjoin('insurance_contracts', 'insurance_contracts.consulting_information_id', 'consulting_information.id')
                                    ->leftjoin('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
                                    ->where('i.parent_id', $o->insurance_agency_id)
                                    ->join('mp_products as p', 'p.id', 'consulting_information.main_product_id')
                                    ->whereNotIn('p.insurance_company_id',[1]);
                                    $child7_for= $child7->get();
                                    $list = $list->union($child7);
                                    foreach ($child7_for as $key => $u){
                                        $child8 = ConsultingInformation::select('consulting_information.*',
                                        'c.email as email', 'c.name as name', 'c.phone_number as phone_number','i.parent_id','i.level_id')
                                        ->leftjoin('customers as c', 'c.id', 'consulting_information.customer_id')
                                        ->leftjoin('insurance_contracts', 'insurance_contracts.consulting_information_id', 'consulting_information.id')
                                        ->leftjoin('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
                                        ->where('i.parent_id', $u->insurance_agency_id)
                                        ->join('mp_products as p', 'p.id', 'consulting_information.main_product_id')
                                        ->whereNotIn('p.insurance_company_id',[1]);
                                        $child8_for= $child8->get();
                                        $list = $list->union($child8);
                                        foreach ($child8_for as $key => $j){
                                            $child9 = ConsultingInformation::select('consulting_information.*',
                                            'c.email as email', 'c.name as name', 'c.phone_number as phone_number','i.parent_id','i.level_id')
                                            ->leftjoin('customers as c', 'c.id', 'consulting_information.customer_id')
                                            ->leftjoin('insurance_contracts', 'insurance_contracts.consulting_information_id', 'consulting_information.id')
                                            ->leftjoin('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
                                            ->where('i.parent_id', $j->insurance_agency_id)
                                            ->join('mp_products as p', 'p.id', 'consulting_information.main_product_id')
                                            ->whereNotIn('p.insurance_company_id',[1]);
                                            $child9_for= $child9->get();
                                            $list = $list->union($child9);
                                            foreach ($child9_for as $key => $r){
                                                $child10 = ConsultingInformation::select('consulting_information.*',
                                                'c.email as email', 'c.name as name', 'c.phone_number as phone_number','i.parent_id','i.level_id')
                                                ->leftjoin('customers as c', 'c.id', 'consulting_information.customer_id')
                                                ->leftjoin('insurance_contracts', 'insurance_contracts.consulting_information_id', 'consulting_information.id')
                                                ->leftjoin('insurance_agencies as i', 'i.id', 'consulting_information.insurance_agency_id')
                                                ->where('i.parent_id', $r->insurance_agency_id)
                                                ->join('mp_products as p', 'p.id', 'consulting_information.main_product_id')
                                                ->whereNotIn('p.insurance_company_id',[1]);
                                                $list = $list->union($child10);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        $list = $list->orderBy('level_id','DESC')->orderBy('created_at','DESC')->get();
        $total = $list->count();
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 50;
        $user = Auth::user();
        if($user->group_id != config('core.group_id.agency')){
            $paginator = new LengthAwarePaginator($list->forPage($currentPage,$perPage), $total, $perPage, $currentPage,['path' => route('consulting.listF1',$id)]);
        }else {
            $paginator = new LengthAwarePaginator($list->forPage($currentPage,$perPage), $total, $perPage, $currentPage,['path' => route('consulting.listmenuf1')]);
        }
        return $paginator;
    }


    public static function searchNoConsultation($params,$agencyId = null) {
        if($agencyId == null){
            $check = !empty($params['type_insurance']) ? $params['type_insurance'] : 1;
            if($check == 1){
                $type_insurance = [1];
            }else{
                $type_insurance = [2,3];
            }
            $list = InsuranceAgency::select('insurance_agencies.*')
            ->where('u.status', '!=', 3)
            ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id')
            ->whereNotIn('insurance_agencies.id',
                function($query) use($type_insurance) {  
                    $query->select('insurance_agency_id')
                        ->from('consulting_information as c')
                        ->join('mp_products as m','m.id','c.main_product_id')
                        ->whereIn('m.insurance_company_id', $type_insurance)
                        ->distinct();
                }
            );
            if (!empty($params["code_agency"])) {
                $list = $list->where('insurance_agencies.code_agency', $params["code_agency"]);
            }

            if (!empty($params["name"])) {
                $list = $list->where('insurance_agencies.name', 'like', '%'.$params["name"].'%');
            }

            if (!empty($params["level"])) {
                $list = $list->where('insurance_agencies.level_id', $params["level"]);
            }

            if (!empty($params["email"])) {
                $list = $list->where('u.email', $params["email"]);
            }

            if (!empty($params["phone"])) {
                $list = $list->where('insurance_agencies.phone', $params["phone"]);
            }
        }else{
            $check = !empty($params['type_insurance']) ? $params['type_insurance'] : 0;
            if($check == 1){
                $type_insurance = [1];
            }elseif($check == 0){
                $type_insurance = [1,2,3];
            }else{
                $type_insurance = [2,3];
            }
            $user = Auth::user();
            if($user->group_id != config('core.group_id.agency')) {
                $agencyList = InsuranceAgency::get()->pluck('id')->toArray();
            }else {
                $insurance_agency = $user->insurance_agency;
                $parentId = [$insurance_agency->id];
                $agencyTree = [$insurance_agency->id];                        
                while (count($parentId) > 0){
                    $childs = InsuranceAgency::selectRaw("insurance_agencies.*")
                                        ->whereIn('insurance_agencies.parent_id', $parentId)
                                        ->whereNotNull('insurance_agencies.code_agency')
                                        ->where('users.status','<>', 3)
                                        ->leftjoin('users', 'users.id', 'insurance_agencies.user_id')
                                        ->get();
                        $parentId = [];
                        if(!empty($childs)){
                            foreach($childs as $child){
                                array_push($agencyTree, $child->id);
                                array_push($parentId, $child->id);
                        }
                    }
                }
                $agencyList = InsuranceAgency::whereIn('id', $agencyTree)->get()->pluck('id')->toArray();
            }
            $list = InsuranceAgency::select('insurance_agencies.*')
            ->where('u.status', '!=', 3)
            ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id')
            ->whereNotIn('insurance_agencies.id',
                function($query) use($type_insurance) {  
                    $query->select('insurance_agency_id')
                        ->from('consulting_information as c')
                        ->join('mp_products as m','m.id','c.main_product_id')
                        ->whereIn('m.insurance_company_id', $type_insurance)
                        ->distinct();
                }
            )
            ->whereIn('insurance_agencies.parent_id', $agencyList);
            $listFor = $list->get();
            if (!empty($params["code_agency"])) {
                $list = $list->where('insurance_agencies.code_agency', $params["code_agency"]);
            }

            if (!empty($params["name"])) {
                $list = $list->where('insurance_agencies.name', 'like', '%'.$params["name"].'%');
            }

            if (!empty($params["level"])) {
                $list = $list->where('insurance_agencies.level_id', $params["level"]);
            }

            if (!empty($params["email"])) {
                $list = $list->where('u.email', $params["email"]);
            }

            if (!empty($params["phone"])) {
                $list = $list->where('insurance_agencies.phone', $params["phone"]);
            }
        }    
        $list = $list->orderBy('level_id','DESC')->orderBy('created_at','DESC')->get();
        $total = $list->count();
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 50;
        $paginator = new LengthAwarePaginator($list->forPage($currentPage,$perPage), $total, $perPage, $currentPage,['path' => route('consulting.show-list-agency-no-consultation')]);
        return $paginator;
    }
    public static function searchNoNonLife($params) {
        $list = InsuranceAgency::select('insurance_agencies.*')
        ->where('u.status', '!=', 3)
        ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id')
        ->whereNotIn('insurance_agencies.id',
            function($query) {  
                $query->select('insurance_agency_id')
                    ->from('consulting_information as c')
                    ->join('mp_products as m','m.id','c.main_product_id')
                    ->where('m.insurance_company_id','!=', 1)
                    ->distinct();
            }
        );
        if (!empty($params["code_agency"])) {
            $list = $list->where('insurance_agencies.code_agency', $params["code_agency"]);
        }

        if (!empty($params["name"])) {
            $list = $list->where('insurance_agencies.name', 'like', '%'.$params["name"].'%');
        }

        if (!empty($params["level"])) {
            $list = $list->where('insurance_agencies.level_id', $params["level"]);
        }

        if (!empty($params["email"])) {
            $list = $list->where('u.email', $params["email"]);
        }

        if (!empty($params["phone"])) {
            $list = $list->where('insurance_agencies.phone', $params["phone"]);
        }
        $list = $list->orderBy('c.effective_date','DESC')->orderBy('created_at','DESC');
        return $list->paginate(50);
    }
}