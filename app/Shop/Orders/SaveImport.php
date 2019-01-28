<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Shop\Orders;

use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Orders\Repositories\OrderRepository;
use App\Shop\VoucherCodes\Repositories\VoucherCodeRepository;
use App\Shop\Couriers\Repositories\CourierRepository;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\Addresses\Repositories\AddressRepository;

/**
 * Description of SaveImport
 *
 * @author michael.hampton
 */
class SaveImport {

    /**
     * 
     * @return boolean
     */
    public function saveBulkImport(
    ChannelRepository $objChannelRepo, OrderRepository $orderRepo, VoucherCodeRepository $voucherRepo, CourierRepository $courierRepo, CustomerRepository $customerRepo, Addressrepository $addressRepo, $arrOrder
    ) {

        if (isset($arrOrder['channel']['id'])) {
            $arrOrder['channel'] = $objChannelRepo->findChannelById($arrOrder['channel']['id']);
        }

        $arrProducts = $arrOrder['products'];
        unset($arrOrder['products']);

        if (isset($arrOrder['voucher_id']['id'])) {
            $arrOrder['voucher_id'] = $voucherRepo->findVoucherCodeById($arrOrder['voucher_id']['id']);
        }

        $order = $orderRepo->createOrder($arrOrder, $voucherRepo, $courierRepo, $customerRepo, $addressRepo);
        $orderRepo = new OrderRepository($order);
        $orderRepo->buildOrderLinesForManualOrder($arrProducts);
        return true;
    }

}
