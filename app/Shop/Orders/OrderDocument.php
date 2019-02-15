<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Shop\Orders;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;

/**
 * Description of OrderDocument
 *
 * @author michael.hampton
 */
class OrderDocument extends Model {

    protected $table = 'order_documents';
    
    protected $fillable = [
        'order_id',
        'file_content'
    ];

}
