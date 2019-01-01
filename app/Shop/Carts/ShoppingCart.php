<?php

namespace App\Shop\Carts;

use Gloudemans\Shoppingcart\Cart;
use Gloudemans\Shoppingcart\CartItem;
use App\Shop\Vouchers\Voucher;

class ShoppingCart extends Cart {

    public static $defaultCurrency;
    protected $session;
    protected $event;

    public function __construct() {
        $this->session = $this->getSession();
        $this->event = $this->getEvents();
        parent::__construct($this->session, $this->event);

        self::$defaultCurrency = strtoupper(config('cart.currency'));
    }

    public function getSession() {
        return app()->make('session');
    }

    public function getEvents() {
        return app()->make('events');
    }

    /**
     * Get the total price of the items in the cart.
     * @param type $decimals
     * @param type $decimalPoint
     * @param type $thousandSeparator
     * @param type $shipping
     * @param \App\Shop\Carts\Voucher $voucher
     * @return type
     */
    public function total($decimals = null, $decimalPoint = null, $thousandSeparator = null, $shipping = 0.00, Voucher $voucher = null) {
        $content = $this->getContent();

        $total = $content->reduce(function ($total, CartItem $cartItem) {
            return $total + ($cartItem->qty * $cartItem->priceTax);
        }, 0);

        $grandTotal = $total + $shipping;

        if (!is_null($voucher)) {
            $newTotal = $this->calculateVoucherAmount($voucher, $grandTotal);

            if ($newTotal !== false) {
                $grandTotal = $newTotal;
            }
        }

        return number_format($grandTotal, $decimals, $decimalPoint, $thousandSeparator);
    }

    /**
     * 
     * @param \App\Shop\Carts\Voucher $voucher
     * @param type $grandTotal
     * @return boolean
     */
    public function calculateVoucherAmount(Voucher $voucher, $grandTotal) {

        $voucher->amount = number_format($voucher->amount, 2);

        if (empty($voucher->amount) || $voucher->amount <= 0) {
            return false;
        }

        if (strtolower($voucher->amount_type) === 'percentage') {

            $newprice = $grandTotal - ($grandTotal * ($voucher->amount / 100));
            $reducedAmount = $grandTotal - $newprice;
            request()->session()->put('discount_amount', number_format($reducedAmount, 2));
        } else {

            $newprice = $grandTotal -= $voucher->amount;
        }

//        if($newprice < 0) {
//            
//            return false;
//        }

        return $newprice;
    }

}
