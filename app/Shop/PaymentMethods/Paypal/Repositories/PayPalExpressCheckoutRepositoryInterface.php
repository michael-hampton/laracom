<?php

namespace App\Shop\PaymentMethods\Paypal\Repositories;

use Illuminate\Http\Request;
use App\Shop\Customers\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Shop\Addresses\Repositories\Interfaces\AddressRepositoryInterface;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\CourierRates\Repositories\Interfaces\CourierRateRepositoryInterface;
use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;
use App\Shop\Channels\Channel;
use App\Shop\Couriers\Courier;
use App\Shop\Orders\Order;


interface PayPalExpressCheckoutRepositoryInterface {

    
    public function getApiContext();

  /**
   * 
   * @param type $shippingFee
   * @param type $voucher
   * @param Request $request
   * @param VoucherCodeRepositoryInterface $voucherCodeRepository
   * @param CourierRepositoryInterface $courierRepository
   * @param CustomerRepositoryInterface $customerRepository
   * @param AddressRepositoryInterface $addressRepository
   * @param CourierRateRepositoryInterface $courierRateRepository
   */
    public function process(
            $shippingFee = 0, 
            $voucher, 
            Request $request, 
            VoucherCodeRepositoryInterface $voucherCodeRepository, 
            Courier $courier,
            CourierRepositoryInterface $courierRepository, 
            CustomerRepositoryInterface $customerRepository, 
            AddressRepositoryInterface $addressRepository, 
            CourierRateRepositoryInterface $courierRateRepository,
            Channel $channel
            );

   /**
    * 
    * @param Request $request
    */
    public function execute(Request $request, Order $order);
}
