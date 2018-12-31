<?php

namespace App\Shop\Roles;

//use Laratrust\Models\LaratrustRole;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description'
    ];
}
