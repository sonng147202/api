<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class CourseAgencies extends Model
{
    protected $table = 'course_agencies';
    protected $fillable = [
        'tutor_course_id',
        'agency_id',
        'result',
        'bonus_points'   
    ];
    public function agency()
    {
        return $this->belongsTo('App\Models\InsuranceAgency', 'agency_id');
    }
    public function tutor_course()
    {
        return $this->belongsTo('App\Models\TutorCourse', 'tutor_course_id');
    }
    public static function searchIndex($id) {
        $list = CourseAgencies::orderBy('created_at','asc')
        ->join('tutor_course','tutor_course.id','course_agencies.tutor_course_id')
        ->join('insurance_agencies','insurance_agencies.code_agency','course_agencies.agency_id')
        ->select('insurance_agencies.code_agency','insurance_agencies.name','course_agencies.*')
        ->where('tutor_course.id',$id)
        ;
        
        // if (!empty($params["name_tutor"])) {
        //     $list = $list->where('tutor.name_tutor', 'like', '%'.$params["name_tutor"].'%');
        // }
        // if (!empty($params["name_course"])) {
        //     $list = $list->where('course.name_course','like', '%'.$params["name_course"].'%');
        // }
        // if (isset($params["expected_day"]) == true ) {
        //     $expected_day = date("Y-m-d",strtotime(str_replace('/', '-', $params["expected_day"])));
        //     $list = $list->where('expected_day',$expected_day);
        // }
        // if (isset($params["start_day"]) == true) {
        //     $start_day = date("Y-m-d",strtotime(str_replace('/', '-', $params["start_day"])));
        //     $list = $list->where('start_day',$start_day);
        // }
        // if (isset($params["training_form"]) == true) {
        //     $list = $list->where('training_form',$params["training_form"]);
        // }
        // if (isset($params["status"]) == true) {
        //     $list = $list->where('tutor_course.status',$params["status"]);
        // }
        return $list->paginate(50);
    }
}