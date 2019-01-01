<?php

namespace App\Shop\Carts;

use Gloudemans\Shoppingcart\Cart;
use Gloudemans\Shoppingcart\CartItem;

class ShoppingCart extends Cart {

    public static $defaultCurrency;
    protected $session;
    protected $event;

    public function __construct() {
        $this->session = $this->getSession();
        $this->event = $this->getEvents();
        parent::__construct($this->session, $this->event);

        self::$defaultCurrency = config('cart.currency');
    }

    public function getSession() {
        return app()->make('session');
    }

    public function getEvents() {
        return app()->make('events');
    }

    /**
     * Get the total price of the items in the cart.
     *
     * @param int $decimals
     * @param string $decimalPoint
     * @param string $thousandSeparator
     * @param float $shipping
     * @param float $voucherAmount
     * @return string
     */
    public function total($decimals = null, $decimalPoint = null, $thousandSeparator = null, $shipping = 0.00, $voucher = null) {
        $content = $this->getContent();

        $total = $content->reduce(function ($total, CartItem $cartItem) {
            return $total + ($cartItem->qty * $cartItem->priceTax);
        }, 0);

        $grandTotal = $total + $shipping;
       
        if(!is_null($voucher)){
            $newTotal = $this->calculateVoucherAmount($voucher, $grandTotal);
        
            if($newTotal !== false) {
                $grandTotal = $newTotal;
            }
        }

        return number_format($grandTotal, $decimals, $decimalPoint, $thousandSeparator);
    }
    
    public function calculateVoucherAmount(Voucher $voucher, $grandTotal) {
        
        if (empty($voucher->amount) || $voucher->amount <= 0) {
        return false;
            
        }
        
        if(strtolower($voucher->amount_type) === 'percent') {
            $newprice = $grandTotal - ($grandTotal * ($voucher->amount/100));
            
        } else {
            $newprice = $grandTotal -= $voucherAmount;
        }
        
        if($newprice < 0) {
            
            return false;
        }
        
        return $newprice;
    }

}
