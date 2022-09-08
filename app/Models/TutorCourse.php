<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;

class TutorCourse extends Model
{
    protected $table = 'tutor_course';
    protected $fillable = [
        'tutor_id',
        'course_id',
        'address',
        'training_form',
        'status',
        'start_time',
        'end_time'
    ];
    public function course()
    {
        return $this->belongsTo('App\Models\Course', 'course_id');
    }
    public function tutor()
    {
        return $this->belongsTo('App\Models\Tutor', 'tutor_id');
    }

    public static function searchIndex($params) {
        $list = TutorCourse::orderBy('created_at','desc')
        ->join('course','course.id','tutor_course.course_id')
        ->join('tutor','tutor.id','tutor_course.tutor_id')
        ->select('tutor.name_tutor','course.name_course','tutor_course.*')
        ;
        
        if (!empty($params["name_tutor"])) {
            $list = $list->where('tutor.name_tutor', 'like', '%'.$params["name_tutor"].'%');
        }
        if (!empty($params["name_course"])) {
            $list = $list->where('course.name_course','like', '%'.$params["name_course"].'%');
        }
        if (isset($params["end_time"]) == true ) {
            $end_time = date("Y-m-d",strtotime(str_replace('/', '-', $params["end_time"])));
            $list = $list->where('end_time',$expected_day);
        }
        if (isset($params["start_time"]) == true) {
            $start_time = date("Y-m-d",strtotime(str_replace('/', '-', $params["start_time"])));
            $list = $list->where('start_time',$start_day);
        }
        if (isset($params["training_form"]) == true) {
            $list = $list->where('training_form',$params["training_form"]);
        }
        if (isset($params["status"]) == true) {
            $list = $list->where('tutor_course.status',$params["status"]);
        }
        return $list->paginate(50);
    }
    public static function searchIndexTutor($params,$id) {
        $list = TutorCourse::orderBy('created_at','desc')
        ->join('course','course.id','tutor_course.course_id')
        ->join('tutor','tutor.id','tutor_course.tutor_id')
        ->select('tutor.name_tutor','course.name_course','tutor_course.*')
        ->where('tutor_course.tutor_id',$id)
        ;
        if (!empty($params["name_course"])) {
            $list = $list->where('course.name_course','like', '%'.$params["name_course"].'%');
        }
        if (isset($params["expected_day"]) == true ) {
            $expected_day = date("Y-m-d",strtotime(str_replace('/', '-', $params["expected_day"])));
            dd($expected_day);
            $list = $list->where('expected_day',$expected_day);
        }
        if (isset($params["start_day"]) == true) {
            $start_day = date("Y-m-d",strtotime(str_replace('/', '-', $params["start_day"])));
            $list = $list->where('start_day',$start_day);
        }
        if (isset($params["training_form"]) == true) {
            $list = $list->where('training_form',$params["training_form"]);
        }
        if (isset($params["status"]) == true) {
            $list = $list->where('tutor_course.status',$params["status"]);
        }
        return $list->paginate(50);
    }
}