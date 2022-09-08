<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageCategory extends Model
{
    protected $table = 'image_categories';
    protected $fillable = ['name'];

    public function images()
    {
    	return $this->hasMany('App\Models\Image', 'image_category_id');
    }
}
