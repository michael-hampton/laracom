<?php
namespace App\Shop\Channels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
/**
 * 
 */
class ChannelPaymentDetails extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'channel_id',
        'data'
    ];
    
    protected $table = 'channel_payment_details';
   
   /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
}
