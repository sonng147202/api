<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'user_permistions';

    protected $fillable = ["user_id", "permission"];

    public static function getUserPermistion($userId) {
        return self::where('user_id', $userId)->pluck('permission')->toArray();
    }
}
