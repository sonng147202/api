<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TypeSupportCustemer extends Model
{
    protected $table = 'type_support_custemer';
    protected $fillable = [
        'name','description', 'status'
    ];


    public function type_support_custemer_image()
    {
        return $this->hasOne('App\Models\TypeSupportCustemerImage', 'type_support_custemer_id');
    }

}
