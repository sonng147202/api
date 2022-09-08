<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Product\Models\ProductCommission;

class InsuranceCompany extends Model
{
    use SoftDeletes;

    protected $table = 'insurance_companies';

    protected $fillable = [
        'name', 'parent_id', 'description','brand_logo',
        'email', 'tax_code','phone_number','status','address',
        'representative_name', 'representative_phone','representative_email'
    ];

    /**
     * Get list active companies
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getListActive()
    {
        return self::where('parent_id',0)->get();
    }

    public static function getListSubsidiaries($parent_id)
    {
        if (!empty($parent_id)){
            return Company::where('parent_id',$parent_id)->with('parentCompany')->get()->toArray();
        } return [];
    }


    public static function searchIndex($params)
    {
        $list = InsuranceCompany::where('status','!=',0);

        if (!empty($params["name"])) {
            $list = $list->where('insurance_companies.name', 'like', '%'.$params["name"].'%');
        }
        
        if (!empty($params["email"])) {
            $list = $list->where('insurance_companies.email', 'like', '%'.$params["email"].'%');
        }

        if (!empty($params["phone_number"])) {
            $list = $list->where('insurance_companies.phone_number', $params["phone_number"]);
        }

        $list= $list->orderBy('created_at','desc');

        return $list;
    }


    /**
     * Get all companies
     */
    public static function updateById($id, $data)
    {
        return self::where('id', $id)->update($data);
    }

    /**
     * @param $id
     * @return bool
     */
    public static function deleteById($id)
    {
        return self::where('id', $id)->update(['status' => 0]);
    }
}
