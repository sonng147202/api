<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdIssuePlaces extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'id_issue_places';
    protected $fillable = [
        'name','name_without_accent','order_in_list'
    ];
}
