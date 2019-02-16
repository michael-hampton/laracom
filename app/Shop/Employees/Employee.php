<?php

namespace App\Shop\Employees;

use App\Shop\Channels\Channel;
use App\Shop\Roles\Role;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laratrust\Traits\LaratrustUserTrait;
use Watson\Validating\ValidatingTrait;

class Employee extends Authenticatable {

    use Notifiable,
        SoftDeletes,
        LaratrustUserTrait,
        ValidatingTrait;

    /**
     *
     * @var type 
     */
    protected $rules = [
        'create' => [
            'name'     => ['required'],
            'email'    => ['required', 'email', 'unique:employees'],
            'password' => ['required', 'min:8']
        ],
        'update' => [
            'name'  => ['required'],
            'email' => ['required', 'email']
        ]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $dates = ['deleted_at'];

    public function channels() {
        return $this->belongsToMany(Channel::class);
    }

    public function roles() {
        return $this->belongsToMany(Role::class);
    }

    /**
     * 
     * @param type $blUpdate
     * @return boolean
     */
    public function validate($blUpdate = false) {
        
        $rules = $blUpdate === false ? $this->rules['create'] : $this->rules['update'];
        
        $this->setRules($rules);
        $blValid = $this->isValid();

        if (!$blValid)
        {
            $this->validationFailures = $this->getErrors()->all();

            return false;
        }

        return true;
    }

    /**
     * 
     * @return type
     */
    public function getValidationFailures() {
        return $this->validationFailures;
    }

}
