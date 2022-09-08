<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\OauthAccessToken;
use Modules\Product\Models\ProductLevelCommission;
use App\Models\InsuranceContract;
use App\Models\AgencyWallet;
use App\Models\AgencyComment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;

class InsuranceAgency extends Model
{
    protected $table = 'insurance_agencies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','phone','parent_id', 'user_id','level_id', 'code_agency_invite', 'office_id', 'description', 'address', 'avatar', 'code_agency','manager_id', 'commission_id', 'agency_company_id', 'agency_company_is_manager','province_id', 'district_id', 'ward_id','type','id_card_number','sex','tax_code', 
        'bank_account_number','bank_account_name','bank_id', 'started_at', 'img_id_card_number', 'img_register_form', 'img_service_contract', 'img_personal', 'img_passport', 'sale_type_id', 'personal_revenue_by_month', 'branch_revenue_by_month', 'child_agency', 'child_f1', 'child_id_f1', 'revenue_month', 
        'parent_number', 'birthday','last_contract','status_info','pdf_agency_register','signature','register_online','place_card_number','date_card_number', 'pdf_offer','contract_advise','letter_accept','contract_number','contract_date','expiration_date','invite_id','qualifying_date','appoint_date','status_appoint','pdf_appoint','status_mail_appoint','status_file'

    ];

    public function parent()
    {
        return $this->belongsTo('App\Models\InsuranceAgency', 'parent_id');
    }

    public function office()
    {
        return $this->belongsTo('App\Models\Office', 'office_id');
    }

    public function bank()
    {
        return $this->belongsTo('App\Models\Bank', 'bank_id');
    }

    public function province()
    {
        return $this->belongsTo('App\Models\Province', 'province_id');
    }

    public function user()
    {
        return $this->belongsTo('Modules\Core\Models\User', 'user_id');
    }

    public function manager ()
    {
        return $this->belongsTo('Modules\Core\Models\User','manager_id');
    }
    public function revenue ()
    {
        return $this->hasOne('App\Models\Revenue','isurance_agency_id');
    }

    public function revenueMonthlys ()
    {
        return $this->hasMany('App\Models\RevenueMonthly');
    }


    public function inherits ()
    {
        return $this->hasMany('App\Models\InsuranceAgencyInherit');
    }

    public function company ()
    {
        return $this->belongsTo('App\Models\InsuranceCompany','agency_company_id');
    }
    public function insuranceContracts()
    {
        return $this->hasMany('App\Models\InsuranceContract', 'sale_type_id');
    }
    public function agencyWallet()
    {
        return $this->hasOne('App\Models\AgencyWallet', 'id_agencies');
    }
    public function agencyWalletExchange()
    {
        return $this->hasMany('App\Models\AgencyWalletExchange', 'id_agencies');
    }
    public function rewardWallet()
    {
        return $this->hasOne('App\Models\RewardWallet', 'id_agencies');
    }
    public function rewardWalletExchange()
    {
        return $this->hasMany('App\Models\RewardWalletExchange', 'id_agencies');
    }
    public function withdrawalExchange()
    {
        return $this->hasMany('App\Models\WithdrawalExchange', 'id_agencies');
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
            if(Product::find($key->product_id)->is_agency != 0 && $key->updated_at >= $first_month) {
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
            if($key7){
                array_push($contract_branch, $key7->id);
            }

            // Thêm
            if($key7->id != 0){
                $record_cap_6 = InsuranceAgency::where('parent_id', $key7->id)->get();
                foreach ($record_cap_6 as $key6) {
                    if($key6){
                        array_push($contract_branch, $key6->id);
                    }

                    if($key6->id != 0){
                        $record_cap_5 = InsuranceAgency::where('parent_id', $key6->id)->get();
                        foreach ($record_cap_5 as $key5) {
                            if($key5){
                                array_push($contract_branch, $key5->id);
                            }
            // Thêm

                            if($key5->id != 0){
                                $record_cap_4 = InsuranceAgency::where('parent_id', $key5->id)->get();
                                foreach ($record_cap_4 as $key4) {
                                    if($key4){
                                        array_push($contract_branch, $key4->id);
                                    }

                                    if($key4->id != 0){
                                        $record_cap_3 = InsuranceAgency::where('parent_id', $key4->id)->get();
                                        foreach ($record_cap_3 as $key3) {
                                            if($key3){
                                                array_push($contract_branch, $key3->id);
                                            }

                                            if($key3->id != 0){
                                                $record_cap_2 = InsuranceAgency::where('parent_id', $key3->id)->get();
                                                foreach ($record_cap_2 as $key2) {
                                                    if($key2){
                                                        array_push($contract_branch, $key2->id);
                                                    }

                                                    if($key2->id != 0){
                                                        $record_cap_1 = InsuranceAgency::where('parent_id', $key2->id)->get();
                                                        foreach ($record_cap_1 as $key1) {
                                                            if($key1){
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

    public function parent_number($parent_id, $i = 0) {
        if ($parent_id != 0) {
            $i=$i+1;
            $parent_id = InsuranceAgency::find($parent_id)->parent_id;
            $i = $this->parent_number($parent_id, $i);
        }
        return $i;
    }

    public function getParentNumberAttribute() {
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
            if($key5){
                array_push($contract_branch, $key5->id);
            }
        }
        return $contract_branch;
    }

    public static function getUsersSend($req){
        $levels = isset($req['levels']) ? $req['levels'] : [];
        $date_join_start = isset($req['date_join_start']) ?? '';
        $date_join_end = isset($req['date_join_end']) ?? '';
        $offices = isset($req['offices']) ? $req['offices'] : [];
        $is_office_director = $req['is_office_director'];

        $revenue_type = isset($req['revenue_type']) ?? '';
        $revenue_year = isset($req['revenue_year']) ?? '';
        $revenue_quarter = isset($req['revenue_quarter']) ?? '';;
        $revenue_month = isset($req['revenue_month']) ?? '';;
        $revenue_from = isset($req['revenue_from']) ?? '';;
        $revenue_to = isset($req['revenue_to']) ?? '';;

        $list = DB::table('insurance_agencies as agency')
                    ->select('agency.*', 'u.id as user_id', 'u.email as email', 'l.code as level_code', 'o.name as office_name', 'c.id as contract_id')
                    ->whereNotIn('u.status', [3,5])
                    ->where('agency.parent_id', '!=', 0)
                    ->leftjoin('users as u', 'u.id', 'agency.user_id')
                    ->leftjoin('insurance_contracts as c', 'c.sale_type_id', 'agency.id')
                    ->leftjoin('levels as l', 'l.level', 'agency.level_id')
                    ->leftjoin('offices as o', 'o.id', 'agency.office_id');

        if(!empty($levels)){
            $list = $list->whereIn('agency.level_id', $levels);
        }  

        if(!empty($date_join_start) && !empty($date_join_end)){
            $list = $list->whereBetween('agency.created_at', [$date_join_start.' 00:00:00', $date_join_end.' 23:59:59']);
        }

        if(!empty($offices)){
            if($is_office_director == 1){
                $code_agency = Office::whereIn('id', $offices)->pluck('manager_id')->toArray();
                if(!empty($code_agency)){
                    $list = $list->whereIn('agency.code_agency', $code_agency);
                }
            }else {
                $list = $list->whereIn('agency.office_id', $offices);
            }
        }  

        if(!empty($revenue_type)){
            if($revenue_type == 1){
                //theo năm
                $start = $revenue_year.'-01-01 00:00:00';
                $end = $revenue_year.'-12-31 23:59:59';
            }else if($revenue_type == 2){
                //theo quý
                if($revenue_quarter == 1){
                    $start = $revenue_year.'-01-01 00:00:00';
                    $end = $revenue_year.'-03-31 23:59:59';
                }else if($revenue_quarter == 2){
                    $start = $revenue_year.'-04-01 00:00:00';
                    $end = $revenue_year.'-06-30 23:59:59';
                }else if($revenue_quarter == 3){
                    $start = $revenue_year.'-07-01 00:00:00';
                    $end = $revenue_year.'-09-30 23:59:59';
                }else if($revenue_quarter == 4){
                    $start = $revenue_year.'-10-01 00:00:00';
                    $end = $revenue_year.'-12-31 23:59:59';
                }
            }else if($revenue_type == 3){
                //theo tháng
                $start = $revenue_year.'-'.$revenue_year.'-01 00:00:00';
                $end = $revenue_year.'-'.$revenue_year.'-31 23:59:59';
            }
            $list = $list->whereBetween('c.pass_ack_date', [$start, $end]);
            if(!empty($revenue_from) && !empty($revenue_to)) {
                $list = $list->whereBetween('c.paid_amount', [$revenue_from, $revenue_to]);
            }
        }
        return $list;
    }

    // public function getRevenueMonthAttribute()
    // {
    //     $first_month = date('Y-m-01 00:00:01');
    //     // $revenue = $this->revenueMonthlys->where([['insurance_agency_id', $this->id], ['month', $first_month], ['year', date('Y')]])->select('*', DB::raw('sum(self_revenue) as self_revenue'))->groupBy('insurance_agency_id')->first();
    //     if ($this->revenueMonthlys != null){

    //     $revenue = $this->revenueMonthlys->where('insurance_agency_id', $this->id)->where('month', $first_month)->where('year', date('Y'))->select('*', DB::raw('sum(self_revenue) as self_revenue'))->groupBy('insurance_agency_id')->first();
    //     }
    //     dd($revenue);
    //     $result = $revenue->self_revenue + $revenue->branch_revenue;
    //     return $result;
    // }

    public static function changeAvatar($data, $avatarJson, $img_code_after, $img_code_before)
    {
        $obj = InsuranceAgency::find($data['id']);
        $obj->avatar = $avatarJson;
        if(isset($data['img_code_after'])){
            $obj->img_code_after = $img_code_after;
        }
        if(isset($data['img_code_before'])){
            $obj->img_code_before = $img_code_before;
        }
        $obj->save();
    }

    public static function searchByCondition($params) {
        $p = InsuranceAgency::with("commisison","manager", "agencyWallet");
        if (!empty($params["id"])) {
            $p = $p->orwhere('id', '=', $params["id"]);
        }
        if (!empty($params["name"])) {
            $p = $p->orwhere('name', 'like', '%'.$params["name"].'%');
        }
        if (!empty($params["email"])) {
            $p = $p->orwhere('email', 'like', '%'.$params["email"].'%');
        }
        if (!empty($params["commission_id"])) {
            $p = $p->orwhere('commission_id', $params["commission_id"]);
        }
        if (!empty($params["manager_id"])) {
            $p = $p->orwhere('manager_id', $params["manager_id"]);
        }

        if(!empty($params['export']) && $params['export'] == 1){
            return $p->get();
        }

        return $p->orderBy('created_at', 'desc')->paginate(10);
    }

    public static function searchAgencyOnline($req, $agencyId) {

        $list = InsuranceAgency::where('insurance_agencies.parent_id', $agencyId)
                                ->where('insurance_agencies.register_online', 1)
                                ->select('insurance_agencies.*', 'u.email as email', 'a.status')
                                        ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id')
                                        ->leftjoin('approves as a', 'a.insurance_agency_id', 'insurance_agencies.id')
                                        ->orderBy('insurance_agencies.created_at', 'desc');
        if(!empty($req['code_agency'])){
            $list->where('insurance_agencies.code_agency', $req['code_agency']);
        }

        if(!empty($req['name'])){
            $list->where('insurance_agencies.name', 'like', '%'.$req['name'].'%');
        }

        if(!empty($req['phone'])){
            $list->where('insurance_agencies.phone', 'like', '%'.$req['phone'].'%');
        }

        if(!empty($req['level'])){
            $list->where('insurance_agencies.level_id', $req['level']);
        }

        if(isset($req['register_online_status'])){
            $list->where('a.status', $req['register_online_status']);
        }

        if(isset($req['status_offer'])){
            $list->where('a.status_offer', $req['status_offer']);
        }

        if (!empty($req["email"])) {
            $list = $list->where('u.email', $req["email"]);
        }

        return $list->paginate(15);
    }
    public static function searchAgencyAppoint($params, $agencyId = null) {

        $startDefault = Carbon::now()->startOfMonth()->format('d/m/Y');
        $endDefault = Carbon::now()->endOfMonth()->format('d/m/Y');
        $submit = !empty($params['submit']) ? $params['submit'] : '';
        $date_type = !empty($params['date_type']) ? $params['date_type'] : 0;
        $start = !empty($params['start']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["start"]))).' 00:00:00' : date("Y-m-d",strtotime(str_replace('/', '-', $startDefault))).' 00:00:00';
        $end = !empty($params['end']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["end"]))).' 23:59:59' : date("Y-m-d",strtotime(str_replace('/', '-', $endDefault))).' 23:59:59';

        if(empty($agencyId)){
            $list = InsuranceAgency::where('insurance_agencies.parent_id', '!=', 0)
                ->where('u.status', '!=', 5)
                ->where('insurance_agencies.level_id', '<=', 7)
                ->where('insurance_agencies.level_id', '>', 1)
                ->select('insurance_agencies.*', 'u.email as email')
                ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id')
                ->orderBy('insurance_agencies.level_id', 'desc')
                ->orderBy('insurance_agencies.code_agency', 'asc');
        }else {
                $parentId = [$agencyId];
                $agencyTree = [$agencyId];                        
                while (count($parentId) > 0){
                    $childs = InsuranceAgency::selectRaw("insurance_agencies.*")
                                        ->whereIn('insurance_agencies.parent_id', $parentId)
                                        ->whereNotNull('insurance_agencies.code_agency')
                                        ->where('users.status','<>', 5)
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
            $list = InsuranceAgency::whereIn('insurance_agencies.id', $agencyList)
                ->select('insurance_agencies.*', 'u.email as email')
                ->where('u.status', '!=', 5)
                ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id');
        }
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

        if(empty($params["status"])) {
            $list = $list->where('u.status', '!=' , '5');
        }elseif($params["status"] == 5){
            $list = $list->where('u.status', $params["status"]);
        }elseif ($params["status"] == 1) {
            $list = $list->where('u.status', '!=' ,5);
        }

        if (!empty($params["phone"])) {
            $list = $list->where('insurance_agencies.phone', $params["phone"]);
        }
        if($date_type == 1){
            //Ngày nộp hợp đồng 
            $list = $list->whereBetween('qualifying_date', [$start, $end]);
        }else if($date_type == 2){
            //Ngày ACK + 21
            $list = $list->whereBetween('appoint_date', [$start, $end]);
        }

        return $list->orderBy('appoint_date','desc')->orderBy('level_id', 'desc')->orderBy('id')->paginate(50);
    }
    public static function exportAgencyAppoint($params, $agencyId) {

        $startDefault = Carbon::now()->startOfMonth()->format('d/m/Y');
        $endDefault = Carbon::now()->endOfMonth()->format('d/m/Y');
        $submit = !empty($params['submit']) ? $params['submit'] : '';
        $date_type = !empty($params['date_type']) ? $params['date_type'] : 0;
        $start = !empty($params['start']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["start"]))).' 00:00:00' : date("Y-m-d",strtotime(str_replace('/', '-', $startDefault))).' 00:00:00';
        $end = !empty($params['end']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["end"]))).' 23:59:59' : date("Y-m-d",strtotime(str_replace('/', '-', $endDefault))).' 23:59:59';

        if(empty($agencyId)){
            $list = InsuranceAgency::where('insurance_agencies.parent_id', '!=', 0)
                ->where('u.status', '!=', 5)
                ->where('insurance_agencies.level_id', '<=', 7)
                ->where('insurance_agencies.level_id', '>', 1)
                ->select('insurance_agencies.*', 'u.email as email')
                ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id')
                ->orderBy('insurance_agencies.level_id', 'desc')
                ->orderBy('insurance_agencies.code_agency', 'asc');
        }else {
            $list = InsuranceAgency::where('insurance_agencies.parent_id', $agencyId)
                ->select('insurance_agencies.*', 'u.email as email')
                ->where('u.status', '!=', 3)
                ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id');
        }
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

        if(empty($params["status"])) {
            $list = $list->where('u.status', '!=' , '5');
        }elseif($params["status"] == 5){
            $list = $list->where('u.status', $params["status"]);
        }elseif ($params["status"] == 1) {
            $list = $list->where('u.status', '!=' ,5);
        }

        if (!empty($params["phone"])) {
            $list = $list->where('insurance_agencies.phone', $params["phone"]);
        }
        if($date_type == 1){
            //Ngày nộp hợp đồng 
            $list = $list->whereBetween('qualifying_date', [$start, $end]);
        }else if($date_type == 2){
            //Ngày ACK + 21
            $list = $list->whereBetween('appoint_date', [$start, $end]);
        }

        return $list->orderBy('appoint_date', 'desc')->get();
    }
    public static function searchIndex($params, $agencyId) {

        $startDefault = Carbon::now()->startOfMonth()->format('d/m/Y');
        $endDefault = Carbon::now()->endOfMonth()->format('d/m/Y');
        $submit = !empty($params['submit']) ? $params['submit'] : '';
        $date_type = !empty($params['date_type']) ? $params['date_type'] : 0;
        $start = !empty($params['start']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["start"]))).' 00:00:00' : date("Y-m-d",strtotime(str_replace('/', '-', $startDefault))).' 00:00:00';
        $end = !empty($params['end']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["end"]))).' 23:59:59' : date("Y-m-d",strtotime(str_replace('/', '-', $endDefault))).' 23:59:59';

        $parentId = [$agencyId];
        $agencyTree = [$agencyId];      
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
        $list = InsuranceAgency::whereIn('insurance_agencies.id', $agencyTree)
                ->select('insurance_agencies.id as agency_id', 'u.avatar',
                        'insurance_agencies.code_agency', 'insurance_agencies.name',
                        'u.email as email', 'levels.code as level_code', 
                        'levels.name as level_name', 'insurance_agencies.status')
                ->where('u.status', '!=', 3)
                ->where('insurance_agencies.id', '!=', $agencyId)
                ->leftjoin('levels', 'levels.id', 'insurance_agencies.level_id')
                ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id')
                ->leftjoin('approves as app', 'app.insurance_agency_id', 'insurance_agencies.id');

        if (!empty($params["search"])) {
            $list = $list->where('insurance_agencies.code_agency','like','%'.$params['search'].'%')
                            ->OrWhere('insurance_agencies.name','like','%'.$params['search'].'%');
        }

        return $list->orderBy('date_approve','desc')->paginate(50)->toArray();
    }

    public static function searchAdminIndex($params, $agencyId) {

        $startDefault = Carbon::now()->startOfMonth()->format('d/m/Y');
        $endDefault = Carbon::now()->endOfMonth()->format('d/m/Y');
        $submit = !empty($params['submit']) ? $params['submit'] : '';
        $date_type = !empty($params['date_type']) ? $params['date_type'] : 0;
        $start = !empty($params['start']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["start"]))).' 00:00:00' : date("Y-m-d",strtotime(str_replace('/', '-', $startDefault))).' 00:00:00';
        $end = !empty($params['end']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["end"]))).' 23:59:59' : date("Y-m-d",strtotime(str_replace('/', '-', $endDefault))).' 23:59:59';
        if(empty($agencyId)){
            $list = InsuranceAgency::where('insurance_agencies.parent_id', '!=', 0)
                ->where('u.status', '!=', 3)
                ->select('insurance_agencies.*', 'u.email as email')
                ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id')
                ->leftjoin('approves as app', 'app.insurance_agency_id', 'insurance_agencies.id')
                ->orderBy('insurance_agencies.level_id', 'desc')
                ->orderBy('insurance_agencies.code_agency', 'asc');
        }else {
            $list = InsuranceAgency::where('insurance_agencies.parent_id', $agencyId)
                ->select('insurance_agencies.*', 'u.email as email')
                ->where('u.status', '!=', 3)
                ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id');
        }
        //dd(empty($params["code_agency"]));
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

        if(empty($params["status"])) {
            $list = $list->where('u.status', '!=' , '5');
        }elseif($params["status"] == 5){
            $list = $list->where('u.status', $params["status"]);
        }elseif ($params["status"] == 1) {
            $list = $list->where('u.status', '!=' ,5);
        }

        if (!empty($params["phone"])) {
            $list = $list->where('insurance_agencies.phone', $params["phone"]);
        }
        if($date_type == 1){
            //Ngày duyệt
            $list = $list->whereBetween('app.date_approve', [$start, $end]);
        }
        return $list->orderBy('id','desc')->paginate(50);
    }

    public static function searchIndexAgencyReport($params) {
        $p = InsuranceAgency::with("level")->orderBy('created_at', 'desc');
        if (!empty($params["name"])) {
            $p = $p->where('name', 'like', '%'.$params["name"].'%')->orderBy('created_at', 'desc');
        }
        if (!empty($params["id"])) {
            $p = $p->where('id', '=', $params["id"])->orderBy('created_at', 'desc');
        }
        if (!empty($params["email"])) {
            $p = $p->where('email', 'like', '%'.$params["email"].'%')->orderBy('created_at', 'desc');
        }
        if (!empty($params["level_id"])) {
            $p = $p->where('level_id', $params["level_id"])->orderBy('created_at', 'desc');
        }
        if (!empty($params["fromDate"])) {
            $p = $p->where('created_at', '>', $params["fromDate"]);
        }
        if (!empty($params["toDate"])) {
            $p = $p->where('created_at', '<', $params["toDate"]);
        }

        return $p;
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The relationship
     */
    public function commisison()
    {
        return $this->belongsTo('Modules\Product\Models\Commission', 'commission_id');
    }

    public function commission_levels()
    {
        return $this->hasMany('App\Models\InsuranceAgencyCommissionLevel', 'commission_id', 'commission_id');
    }

    public function commission_level()
    {
        return $this->commission_levels()->latest()->first();
    }
    public function level()
    {
        return $this->belongsTo('App\Models\Level','level_id', 'level');
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
                if(!empty($productCommission)){
                    return $productCommission->commission_amount;
                }
            }
        }

        return 0;
    }

    public function routeNotificationForESms()
    {
        return false;
    }

    /**
     * Get customer devices for push notify
     * @return array
     */
    public function routeNotificationForPushMobile()
    {
        // Get all device for this customer
        return OauthAccessToken::getByAgency($this->id);
    }

    public static function changeAgencyCustomerPassword($params, $object)
    {
        $agency = $object::find($params['id']);
        if ($agency != null) {
            $oldPasswordToCheck = $agency->password;
            if (Hash::check($params['old_password'], $oldPasswordToCheck)) {
                $agency->password = $params['password'];
                $agency->save();
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Get list comment
     */
    public static function getListComment($request)
    {
        $query = AgencyComment::where('agency_id', $request['agency_id'])->orderBy('id','DESC');
        return self::getPagination($query, $request);
    }

    /**
     * count page
     * input: total pages, current page
     */
    public static function countPage($totalPages, $currentPage)
    {
        if ($totalPages <= 10) {
            // less than 10 total pages so show all
            $startPage = 1;
            $endPage = $totalPages;
        } else {
            // more than 10 total pages so calculate start and end pages
            if ($currentPage <= 6) {
                $startPage = 1;
                $endPage = 10;
            } elseif ($currentPage + 4 >= $totalPages) {
                $startPage = $totalPages - 9;
                $endPage = $totalPages;
            } else {
                $startPage = $currentPage - 5;
                $endPage = $currentPage + 4;
            }
        }
        $pages = [];
        for ($i = $startPage; $i<$endPage + 1; $i++) {
            array_push($pages, $i);
        }
        return ['pages'=>$pages];
    }

    /**
     * Get pagination for customer : contract, quotation, activity, comment
     */
    public static function getPagination($query, $request)
    {
        $count = $query->count();
        empty($request['limit']) ? $limit = 10 : $limit = $request['limit'];
        empty($request['page']) ? $page = 1 : $page = $request['page'];
        $offSet = ($limit * $page) - $limit;
        $data = $query->offset($offSet)->limit($limit)->get();
        return ['data'=>$data, 'count'=>$count];
    }

    /**
     * Create comment
     */
    public static function createComment($data)
    {
        $user = Auth::user();
        $comment = new AgencyComment();
        $comment->content = $data['content'];
        $comment->author_id = $user->id;
        $comment->agency_id = $data['agency_id'];
        $comment->save();
    }

    public static function getAgencyInvite($code_agency_invite)
    {
        $data = self::where('code_agency', $code_agency_invite)->first();
        return $data;
    }

    public static function searchExcelIndex($params, $agencyId) {
        $list = InsuranceAgency::where('insurance_agencies.parent_id', $agencyId)
                ->select('insurance_agencies.*', 'u.email as email')
                ->where('u.status', '!=', 5)
                ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id');
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
        

        return $list->get();
    }

    public static function searchExcelAdminIndex($params, $agencyId) {

        $startDefault = Carbon::now()->startOfMonth()->format('d/m/Y');
        $endDefault = Carbon::now()->endOfMonth()->format('d/m/Y');
        $submit = !empty($params['submit']) ? $params['submit'] : '';
        $date_type = !empty($params['date_type']) ? $params['date_type'] : 0;
        $start = !empty($params['start']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["start"]))).' 00:00:00' : date("Y-m-d",strtotime(str_replace('/', '-', $startDefault))).' 00:00:00';
        $end = !empty($params['end']) ? date("Y-m-d",strtotime(str_replace('/', '-', $params["end"]))).' 23:59:59' : date("Y-m-d",strtotime(str_replace('/', '-', $endDefault))).' 23:59:59';
        if(empty($agencyId)){
            $list = InsuranceAgency::where('insurance_agencies.parent_id', '!=', 0)
                ->where('u.status', '!=', 5)
                ->select('insurance_agencies.*', 'u.email as email')
                ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id')
                ->leftjoin('approves as app', 'app.insurance_agency_id', 'insurance_agencies.id')
                ->orderBy('insurance_agencies.level_id', 'desc')
                ->orderBy('insurance_agencies.code_agency', 'asc');
        }else {
            $list = InsuranceAgency::where('insurance_agencies.parent_id', $agencyId)
                ->select('insurance_agencies.*', 'u.email as email')
                ->where('u.status', '!=', 5)
                ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id');
        }

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

        if(empty($params["status"])) {
            $list = $list->where('u.status', '!=' ,5);
        }elseif($params["status"] == 5){
            $list = $list->where('u.status', $params["status"]);
        }

        if (!empty($params["phone"])) {
            $list = $list->where('insurance_agencies.phone', $params["phone"]);
        }
        if($date_type == 1){
            //Ngày duyệt
            $list = $list->whereBetween('app.date_approve', [$start, $end]);
        }

        return $list->get();
    }
    public static function searchLockUserInfo($params){

        $list = InsuranceAgency::where('insurance_agencies.parent_id', '!=', 0)
                ->select('insurance_agencies.*', 'u.email as email')
                ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id')
                ->orderBy('insurance_agencies.id');

        if (!empty($params["code_agency"])) {
            $list = $list->where('insurance_agencies.code_agency', $params["code_agency"]);
        }

        if (!empty($params["name"])) {
            $list = $list->where('insurance_agencies.name', 'like', '%'.$params["name"].'%');
        }

        if (!empty($params["email"])) {
            $list = $list->where('u.email', $params["email"]);
        }

        if (!empty($params["phone"])) {
            $list = $list->where('insurance_agencies.phone', $params["phone"]);
        }
        if (isset($params["status_info"])) {
            $list = $list->where('insurance_agencies.status_info', $params["status_info"]);
        }
        return $list->paginate(50);
    }
}

