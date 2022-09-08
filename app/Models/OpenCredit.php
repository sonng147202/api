<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpenCredit extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'opencredit';
    protected $fillable = [
        'name','birthday','sex','address','income_by','total_incom','city'
    ];
}
