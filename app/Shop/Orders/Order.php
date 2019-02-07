<?php

namespace App\Shop\Orders;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Shop\Addresses\Address;
use App\Shop\Couriers\Courier;
use App\Shop\Customers\Customer;
use App\Shop\Comments\Comment;
use App\Shop\OrderStatuses\OrderStatus;
use App\Shop\PaymentMethods\PaymentMethod;
use App\Shop\Products\Product;
use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use OwenIt\Auditing\Contracts\Auditable;

class Order extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use Eloquence;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reference',
        'courier_id',
        'courier',
        'customer_id',
        'address_id',
        'order_status_id',
        'payment',
        'discounts',
        'total_products',
        'total',
        'tax',
        'total_paid',
        'label_url',
        'tracking_number',
        'total_shipping',
        'customer_ref',
        'voucher_code',
        'is_priority',
        'amount_refunded',
        'channel',
        'invoice_reference',
        'amount_invoiced',
        'transaction_id',
        'payment_captured'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function products() {
        return $this->belongsToMany(Product::class)
                        ->withPivot(['quantity']);
    }

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function courier() {
        return $this->belongsTo(Courier::class);
    }

    public function address() {
        return $this->belongsTo(Address::class);
    }

    public function orderStatus() {
        return $this->belongsTo(OrderStatus::class);
    }

    public function paymentMethod() {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments() {
        return $this->morphMany(Comment::class, 'commentable');
    }

}
