<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use SoftDeletes, Authenticatable, CanResetPassword;

    protected $fillable = [];

    public function notifications() {
        return $this->belongsToMany('App\Models\Notification', 'notification_user', 'notification_id', 'user_id');
      }

    public function setPasswordAttribute($pass)
    {
        $this->attributes['password'] = Hash::make($pass);
    }

    /**
     * Get detail user
     *
     * @param $id
     * @return Model|null|static
     */
    public static function getDetail($id)
    {
        return self::where('id', $id)->first();
    }

    public function insuranceAgency()
    {
        return $this->hasOne('App\Models\InsuranceAgency');
    }
}
