<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class Course extends Model
{
    protected $table = 'course';
    protected $fillable = [
        'name_course',
        'content',
        'time_course',
        'quantity',
        'bonus_points',
        'category_id',
        'status'
    ];

    public function course_category()
    {
        return $this->belongsTo('App\Models\CourseCategory', 'category_id');
    }
    public static function searchIndex($params, $id) {
        $list = Course::orderBy('created_at','desc');
        if($id != null){
            $list = $list->where('category_id',$id);
        }
        if (!empty($params["name_course"])) {
            $list = $list->where('name_course', 'like', '%'.$params["name_course"].'%');
        }
        if (!empty($params["time_course"])) {
            $list = $list->where('time_course',$params["time_course"]);
        }
        if (!empty($params["quantity"])) {
            $list = $list->where('quantity', $params["quantity"]);
        }
        return $list->paginate(50);
    }
}