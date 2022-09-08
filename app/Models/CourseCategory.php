<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class CourseCategory extends Model
{
    protected $table = 'course_category';
    protected $fillable = [
        'name',
        'status'
    ];
    public static function searchIndex($params, $agencyId = null) {
        $list = CourseCategory::orderBy('created_at','desc')->where('status',1);
        if (!empty($params["name"])) {
            $list = $list->where('name', 'like', '%'.$params["name"].'%');
        }
        return $list->paginate(50);
    }
}