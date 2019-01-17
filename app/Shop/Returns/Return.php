<?php
namespace App\Shop\Returns;
use App\Shop\Orders\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sofa\Eloquence\Eloquence;
class Return extends Model {
    use SoftDeletes,
        Eloquence;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'order_id',
        'line_id',
        'quantity',
        'amount',
        'date_refunded',
        'status'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
    
    /**
     *
     * @var type 
     */
    protected $dates = ['deleted_at', 'date_returned'];
    /**
     * 
     * @return type
     */
    public function order() {
        return $this->belongsTo(Order::class);
    }
}
