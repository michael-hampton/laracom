<?php

namespace App\Shop\Channels;

use Illuminate\Database\Eloquent\Model;
use App\Shop\Products\Product;
use App\Shop\Employees\Employee;
use Illuminate\Support\Collection;
use Nicolaslopezj\Searchable\SearchableTrait;

class Channel extends Model {

    use SearchableTrait;

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'channels.name' => 10,
            'channels.description' => 5
        ]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'cover',
        'allocate_on_order',
        'backorders_enabled',
        'send_received_email',
        'send_dispatched_email',
        'status',
        'has_priority'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * 
     * @return type
     */
    public function products() {
        return $this->belongsToMany(Product::class);
    }

    public function employees() {
        return $this->belongsToMany(Employee::class);
    }

    /**
     * @param string $term
     * @return Collection
     */
    public function searchChannel(string $term): Collection {
        return self::search($term)->get();
    }

}
