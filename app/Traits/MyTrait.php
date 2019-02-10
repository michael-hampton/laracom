<?php

namespace App\Traits;

use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\Addresses\Repositories\Interfaces\AddressRepositoryInterface;
use App\Shop\Customers\Repositories\Interfaces\CustomerRepositoryInterface;

trait MyTrait {
    
    private $objVoucherCode;

    /**
     * 
     * @param AddressRepositoryInterface $addressRepo
     * @param type $id
     * @return boolean
     */
    public function validateAddress(AddressRepositoryInterface $addressRepo, $id) {

        try {
            $addressRepo->findAddressById($id);
        } catch (\Exception $e) {
            $this->validationFailures[] = 'Invalid address used';
            return false;
        }
    }

    /**
     * 
     * @param CustomerRepositoryInterface $customerRepo
     * @param type $id
     * @return boolean
     */
    public function validateCustomer(CustomerRepositoryInterface $customerRepo, $id) {

        try {
            $customerRepo->findCustomerById($id);
        } catch (\Exception $e) {

            $this->validationFailures[] = 'Invalid customer used';
            return false;
        }
    }

    /**
     * 
     * @param CourierRepositoryInterface $courierRepo
     * @param type $id
     * @return boolean
     */
    public function validateCourier(CourierRepositoryInterface $courierRepo, $id) {

        try {
            $courierRepo->findCourierById($id);
        } catch (\Exception $e) {
            $this->validationFailures[] = 'Invalid courier used';
            return false;
        }
    }

    /**
     * 
     * @param VoucherCodeRepositoryInterface $voucherRepo
     * @param type $voucherCode
     * @return boolean
     */
    public function validateVoucherCode(VoucherCodeRepositoryInterface $voucherRepo, $voucherCode) {

        if (empty($voucherCode)) {

            return true;
        }
        
        try {
             $this->objVoucherCode = $voucherRepo->findVoucherCodeById($voucherCode);
            
        } catch (\Exception $e) {
            $this->validationFailures[] = 'Invalid voucher code used';
            return false;
        }
    }

    /**
     * 
     * @param type $customerRef
     * @return boolean
     * @throws Exception
     */
    private function validateCustomerRef($customerRef) {

        if (strlen($customerRef) > 36) {
            return false;
        }

        try {
            $result = $this->listOrders()->where('customer_ref', $customerRef);
        } catch (Exception $ex) {
            $this->validationFailures[] = 'Invalid customer ref used';
            throw new Exception($ex->getMessage());
        }

        return $result->isEmpty();
    }

    /**
     * 
     * @param type $data
     * @param type $cartItems
     * @return boolean
     */
    private function validateTotal($data, $cartItems) {
        $productTotal = 0;

        foreach ($cartItems as $cartItem) {

            $productTotal += $cartItem->price;
        }

        $total = $productTotal + $data['total_shipping'] + $data['tax'];

        if (!empty($voucher) && !empty($this->objVoucherCode)) {
                    
            $objVoucher = $voucherRepo->findVoucherById($this->objVoucherCode->voucher_id);
            
            switch($objVoucher->amount_type) {
                    case 'percent':
                        $total = round($total * ((100 - $objVoucher->amount) / 100), 2);
                        break;
                    
                    case 'fixed':
                        $total -= $objVoucher->amount;
                        break;
                }
            //$total -= $data['discounts'];
        }

        if (round($total, 2) !== round($data['total'], 2) || $total < 0) {
            $this->validationFailures[] = 'Invalid totals';
            return false;
        }

        return true;
    }

}
