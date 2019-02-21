<?php

namespace App\Traits;

use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\Addresses\Repositories\Interfaces\AddressRepositoryInterface;
use App\Shop\Customers\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Shop\Vouchers\Voucher;
use App\Shop\Vouchers\Repositories\VoucherRepository;

trait OrderImportValidation {

    /**
     * 
     * @param type $courier
     * @return boolean
     */
    protected function validateCourier($courier) {
        $courier = trim(strtolower($courier));

        $arrCouriers = array_change_key_case($this->arrCouriers->keyBy('name')->toArray(), CASE_LOWER);

        if (!isset($arrCouriers[$courier]))
        {
            $this->arrErrors[$this->lineCount]['courier'] = "Courier is invalid.";
            return false;
        }

        $courierId = $arrCouriers[$courier]['id'];

        $this->courier = $this->arrCouriers[$courierId];
    }

    /**
     * 
     * @param type $customer
     * @return boolean
     */
    protected function validateCustomer($customer) {
        $customer = trim(strtolower($customer));

        if (!isset($this->arrCustomers[$customer]))
        {
            $this->arrErrors[$this->lineCount]['customer'] = "Customer is invalid.";
            return false;
        }

        $this->customer = $this->arrCustomers[$customer];
    }

    /**
     * 
     * @return boolean
     */
    protected function validateCustomerAddress() {

        $customerId = $this->customer['id'];

        if (empty($customerId))
        {

            $this->arrErrors[$this->lineCount]['customer'] = 'Invalid customer';
            return false;
        }

        $objCustomer = $this->objCustomer[$customerId];

        $this->deliveryAddress = $objCustomer->addresses->first();

        return !empty($this->deliveryAddress);
    }

    protected function validateVoucher($order) {
        $voucherCode = trim(strtolower($order['voucher_code']));
        $orderId = trim($order['order_id']);
        $product = trim($order['product']);

        if (empty($voucherCode) || (isset($this->arrOrderVouchers[$orderId]) && in_array($voucherCode, $this->arrOrderVouchers[$orderId])))
        {
            return true;
        }

        $this->objVoucher = $this->voucherCodeRepo->validateVoucherCode($this->channel, $voucherCode, null, $this->voucherRepo, false);
        
        if (!$this->objVoucher)
        {
            $this->arrErrors[$this->lineCount]['voucher_code'] = "Voucher Code is invalid.";
            return false;
        }
        
        $voucherId = $this->objVoucher['voucher_id'];

        if (!isset($this->arrVouchers[$voucherId]))
        {
            $this->arrErrors[$this->lineCount]['voucher_code'] = "Voucher Code is invalid.";
            return false;
        }

        $voucher = $this->arrVouchers[$voucherId];
        $voucherAmount = $voucher->amount;

        if (!isset($this->arrExistingProducts[$product]))
        {

            return false;
        }

        $product = array('product' => $this->arrExistingProducts[$product]);

        if (!$this->validateVoucherScopes($voucher, $product, null, $this->orderTotal))
        {
            $this->arrErrors[$this->lineCount]['voucher_code'] = "Voucher Code is invalid.";
            return false;
        }

        switch ($voucher->amount_type)
        {
            case 'percentage':
                $this->orderTotal = $this->orderTotal - ($this->orderTotal * ($voucherAmount / 100));
                break;
            case 'fixed':
                $this->orderTotal -= $voucherAmount;
                break;
        }

        $this->orderTotal = round($this->orderTotal, 2);
        //$this->voucherAmount = $this->arrVouchers[$voucherId]->amount;
        $this->arrOrderVouchers[$orderId][] = $voucherCode;
        return true;
    }

    /**
     * 
     * @param type $brand
     * @return boolean
     */
    protected function validateProduct($product) {
        $product = trim(strtolower($product));

        $arrExistingProducts = array_change_key_case($this->arrExistingProducts->toArray(), CASE_LOWER);

        if (!isset($arrExistingProducts[$product]))
        {
            $this->arrErrors[$this->lineCount]['product'] = "Product is invalid.";
            return false;
        }
        $this->product = $arrExistingProducts[$product];
    }

    /**
     * 
     * @return boolean
     */
    protected function calculateShippingCost() {
        if (empty($this->courier))
        {
            $this->arrErrors[$this->lineCount]['courier'] = 'invalid courier';
            return false;
        }
        $this->shipping = $this->objCourierRate->findShippingMethod($this->orderTotal, $this->courier, $this->channel, $this->deliveryAddress->country_id);
        $this->shippingCost = 0;
        if (!empty($this->shipping))
        {
            $this->shippingCost = $this->shipping->cost;
        }
        $this->orderTotal += $this->shippingCost;
        return true;
    }

    /**
     * 
     * @param type $channel
     * @return boolean
     */
    protected function validateChannel($channel) {
        $channel = trim($channel);
        $arrChannels = array_change_key_case($this->arrChannels->keyBy('name')->toArray(), CASE_LOWER);
        if (!isset($arrChannels[$channel]))
        {
            $this->arrErrors[$this->lineCount]['channel'] = "Channel is invalid.";
            return false;
        }
        $channelId = $arrChannels[$channel]['id'];
        $this->channel = $this->arrChannels[$channelId];
        return true;
    }

}
