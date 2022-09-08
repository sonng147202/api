<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoCategory extends Model
{
    protected $table = 'video_categories';
    protected $fillable = [
    	'name',
    	'parent_id',
    ];

    public function videos()
    {
    	return $this->hasMany('App\Models\Video', 'video_category_id');
    }
}
