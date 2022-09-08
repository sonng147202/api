<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class Tutor extends Model
{
    protected $table = 'tutor';
    protected $fillable = [
        'name_tutor',
        'email',
        'experience',
        'status',
        'tutor_resources',
        'phone'
    ];
    public static function searchIndex($params) {
        $list = Tutor::orderBy('created_at','desc');
        if (!empty($params["name_tutor"])) {
            $list = $list->where('name_tutor', 'like', '%'.$params["name_tutor"].'%');
        }
        if (!empty($params["email"])) {
            $list = $list->where('email',$params["email"]);
        }
        if (isset($params["experience"]) == true ) {
            $list = $list->where('experience',$params["experience"]);
        }
        if (isset($params["tutor_resources"]) == true) {
            $list = $list->where('tutor_resources',$params["tutor_resources"]);
        }
        if (isset($params["status"]) == true) {
            $list = $list->where('status',$params["status"]);
        }
        return $list->paginate(50);
    }
}