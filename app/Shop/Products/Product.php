<?php

namespace App\Shop\Products;

use App\Shop\Brands\Brand;
use App\Shop\Categories\Category;
use App\Shop\Channels\Channel;
use App\Shop\ProductAttributes\ProductAttribute;
use App\Shop\ProductImages\ProductImage;
use Gloudemans\Shoppingcart\Contracts\Buyable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Nicolaslopezj\Searchable\SearchableTrait;
use Watson\Validating\ValidatingTrait;

class Product extends Model implements Buyable {

    use SearchableTrait,
        ValidatingTrait;

    /**
     *
     * @var type 
     */
    private $validationFailures = [];

    /**
     *
     * @var type 
     */
    protected $rules = [
        'create' => [
            'sku'        => ['required', 'string'],
            'name'       => ['required', 'unique:products'],
            'quantity'   => ['required', 'numeric'],
            'price'      => ['required'],
            'cost_price' => ['required'],
            'brand_id' => ['required'],
        ],
        'update' => [
            'sku'        => ['required', 'string'],
            'name'       => ['required', 'string'],
            'quantity'   => ['required', 'numeric'],
            'price'      => ['required'],
            'cost_price' => ['required'],
            'brand_id' => ['required'],
        ]
    ];
    public $MASS_UNIT = [
        'OUNCES' => 'oz',
        'GRAMS'  => 'gms',
        'POUNDS' => 'lbs'
    ];
    public $DISTANCE_UNIT = [
        'CENTIMETER' => 'cm',
        'METER'      => 'mtr',
        'INCH'       => 'in',
        'MILIMETER'  => 'mm',
        'FOOT'       => 'ft',
        'YARD'       => 'yd'
    ];

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'products.name'        => 10,
            'products.description' => 5
        ]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sku',
        'name',
        'description',
        'cover',
        'quantity',
        'price',
        'brand_id',
        'status',
        'warehouse',
        'weight',
        'mass_unit',
        'status',
        'sale_price',
        'cost_price',
        'length',
        'width',
        'height',
        'distance_unit',
        'slug',
        'reserved_stock'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function categories() {
        return $this->belongsToMany(Category::class);
    }

    public function channels() {
        return $this->belongsToMany(Channel::class);
    }

    /**
     * Get the identifier of the Buyable item.
     *
     * @param null $options
     * @return int|string
     */
    public function getBuyableIdentifier($options = null) {
        return $this->id;
    }

    /**
     * Get the description or title of the Buyable item.
     *
     * @param null $options
     * @return string
     */
    public function getBuyableDescription($options = null) {
        return $this->name;
    }

    /**
     * Get the price of the Buyable item.
     *
     * @param null $options
     * @return float
     */
    public function getBuyablePrice($options = null) {
        return $this->price;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images() {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * @param string $term
     * @return Collection
     */
    public function searchProduct(string $term): Collection {
        return self::search($term)->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attributes() {
        return $this->hasMany(ProductAttribute::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function brand() {
        return $this->belongsTo(Brand::class);
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
