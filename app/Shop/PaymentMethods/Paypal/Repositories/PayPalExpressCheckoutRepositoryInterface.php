<?php

namespace App\Shop\PaymentMethods\Paypal\Repositories;

use Illuminate\Http\Request;

interface PayPalExpressCheckoutRepositoryInterface {

    
    public function getApiContext();

    /**
     * 
     * @param type $shippingFee#
     * @param type $voucherAmount
     * @param Request $request
     */
    public function process($shippingFee, $voucherAmount, Request $request);

    /**
     * 
     * @param Request $request
     */
    public function execute(Request $request);
}
