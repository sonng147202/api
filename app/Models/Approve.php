<?php

namespace App\Models;
use App\Models\InsuranceAgency;
use Illuminate\Database\Eloquent\Model;

class Approve extends Model
{
    protected $table = 'approves';
    protected $fillable = [
        'insurance_agency_id',
        'approver',
        'status',
        'register_online',
        'date_approve',
        'status_offer',
        'date_offer',
    ];

    public function insurance_agency()
    {
        return $this->belongsTo('App\Models\InsuranceAgency', 'insurance_agency_id');
    }
    
    public static function approveRegisterOnlineIndex($req){
        $list = self::where('approves.register_online', 1)
        ->select('approves.*', 'u.email','i.code_agency','i.phone', 'i.level_id')
        ->leftjoin('insurance_agencies as i', 'i.id', 'approves.insurance_agency_id')
        ->leftjoin('users as u', 'u.id', 'i.user_id');

        if(!empty($req['code_agency'])){
            $list->where('i.code_agency', $req['code_agency']);
        }
        if(!empty($req['name'])){
            $list->where('i.name', 'like', '%'.$req['name'].'%');
        }

        if(!empty($req['phone'])){
            $list->where('i.phone', 'like', '%'.$req['phone'].'%');
        }

        if(!empty($req['level'])){
            $list->where('i.level_id', $req['level']);
        }

        if(isset($req['register_online_status'])){
            $list->where('approves.status', $req['register_online_status']);
        }

        if(isset($req['status_offer'])){
            $list->where('approves.status_offer', $req['status_offer']);
        }

        if (!empty($req["email"])) {
            $list = $list->where('u.email', $req["email"]);
        }
        $list = $list->orderBy('approves.status', 'asc')->orderBy('approves.date_approve', 'desc');
        return $list->paginate(50);
    }
    public static function approveSearchIndex($user, $req){
        if($user->group_id != config('core.group_id.agency')){
            $list = self::where('approves.register_online', 0)
                        ->select('approves.*', 'u.email','i.code_agency','i.phone', 'i.level_id')
                        ->leftjoin('insurance_agencies as i', 'i.id', 'approves.insurance_agency_id')
                        ->leftjoin('users as u', 'u.id', 'i.user_id')
                        ->groupBy('approves.insurance_agency_id');
        }else {
            $list = self::where('approves.register_online', 0)
                        ->select('approves.*', 'u.email','i.code_agency','i.phone', 'i.level_id')
                        ->leftjoin('insurance_agencies as i', 'i.id', 'approves.insurance_agency_id')
                        ->leftjoin('users as u', 'u.id', 'i.user_id')
                        ->where('approves.approver', $user->insurance_agency->id);
        }

        if(!empty($req['code_agency'])){
            $list->where('i.code_agency', $req['code_agency']);
        }
        if(!empty($req['name'])){
            $list->where('i.name', 'like', '%'.$req['name'].'%');
        }

        if(!empty($req['phone'])){
            $list->where('i.phone', 'like', '%'.$req['phone'].'%');
        }

        if(!empty($req['level'])){
            $list->where('i.level_id', $req['level']);
        }

        if(isset($req['register_online_status'])){
            $list->where('approves.status', $req['register_online_status']);
        }

        if (!empty($req["email"])) {
            $list = $list->where('u.email', $req["email"]);
        }
        $list = $list->orderBy('approves.status', 'asc')->orderBy('approves.date_approve', 'desc');
        return $list->paginate(50);
    }
    public static function getApproverOnline($insurance_agency_id){
        $data = self::where('insurance_agency_id', $insurance_agency_id)->where('register_online', 1)->first();
        return $data;
    }

    public static function getListApprover($agency, $level){
        $approveList = json_decode($level->approve);
        $data = [];
        $parentId = $agency->parent_id;
        while ($parentId > 0){
            $parentData = InsuranceAgency::where('id', $parentId)->first();
            if(isset($parentData->level->level) && in_array($parentData->level->level, $approveList)) {
                array_push($data, [
                    'insurance_agency_id' => $agency->id,
                    'approver' => $parentData->id
                ]);
            }
            $parentId = $parentData->parent_id;
        }
        if(empty($data)){
            $agencyRoot = InsuranceAgency::where('parent_id', 0)->first();
            array_push($data, [
                'insurance_agency_id' => $agency->id,
                'approver' => $agencyRoot->id,
            ]);
        }
        return $data;
    }

}
