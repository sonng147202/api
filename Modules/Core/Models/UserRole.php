<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $fillable = ["user_id", "role_id"];

    protected $table = 'user_roles';
    /**
     * The relationship
     */
    public function role()
    {
        return $this->belongsTo('Modules\Core\Models\Role', 'role_id');
    }
    public function user()
    {
        return $this->belongsTo('Modules\Core\Models\User', 'user_id');
    }
}
