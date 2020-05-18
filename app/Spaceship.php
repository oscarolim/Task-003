<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Spaceship extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'class', 'crew', 'image', 'value', 'status',
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function armament()
    {
        return $this->belongsToMany('App\Armament')->withPivot('quantity');
    }
}
