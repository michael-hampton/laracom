<?php

namespace App\Shop\PaymentMethods\Paypal\Repositories;

use Illuminate\Http\Request;
use App\Shop\Customers\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Shop\Addresses\Repositories\Interfaces\AddressRepositoryInterface;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;


interface PayPalExpressCheckoutRepositoryInterface {

    
    public function getApiContext();

    /**
     * 
     * @param type $shippingFee#
     * @param type $voucherAmount
     * @param Request $request
     */
    public function process($shippingFee, $voucher, Request $request);

   /**
    * 
    * @param Request $request
    * @param \App\Shop\PaymentMethods\Paypal\Repositories\VoucherCodeRepositoryInterface $voucherCodeRepository
    * @param \App\Shop\PaymentMethods\Paypal\Repositories\CourierRepositoryInterface $courierRepository
    * @param \App\Shop\PaymentMethods\Paypal\Repositories\CustomerRepositoryInterface $customerRepository
    * @param \App\Shop\PaymentMethods\Paypal\Repositories\AddressRepositoryInterface $addressRepository
    */
    public function execute(Request $request, VoucherCodeRepositoryInterface $voucherCodeRepository, CourierRepositoryInterface $courierRepository, CustomerRepositoryInterface $customerRepository, AddressRepositoryInterface $addressRepository);
}
