<?php

namespace App\Traits;

use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\Addresses\Repositories\Interfaces\AddressRepositoryInterface;
use App\Shop\Customers\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Shop\Vouchers\Voucher;
use App\Shop\Vouchers\Repositories\VoucherRepository;
use App\Shop\Addresses\Address;

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
            $address = $addressRepo->findAddressById($id);

            if (!$this->validatePostcode($address))
            {
                return false;
            }
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

        if (empty($voucherCode))
        {

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

        if (strlen($customerRef) > 36)
        {
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
        $subtotal = 0;

        foreach ($cartItems as $cartItem)
        {

            $subtotal += $cartItem->price;
        }

        if (!empty($this->objVoucherCode))
        {

            $objVoucher = (new VoucherRepository(new Voucher))->findVoucherById($this->objVoucherCode->voucher_id);

            switch ($objVoucher->amount_type)
            {
                case 'percentage':
                    $subtotal = round($subtotal * ((100 - $objVoucher->amount) / 100), 2);
                    break;

                case 'fixed':
                    $total -= $objVoucher->amount;
                    break;
            }
            //$total -= $data['discounts'];
        }

        $total = $subtotal += $data['total_shipping'];

        if (round($total, 2) !== round($data['total'], 2) || $total < 0)
        {
            $this->validationFailures[] = 'Invalid totals';
            return false;
        }

        return true;
    }

    /**
     * 
     * @param Address $address
     * @return boolean
     */
    public function validatePostcode(Address $address) {

        $ZIPREG = array(
            "US" => "^\d{5}([\-]?\d{4})?$",
            225  => "^(GIR|[A-Z]\d[A-Z\d]??|[A-Z]{2}\d[A-Z\d]??)[ ]??(\d[A-Z]{2})$",
            "DE" => "\b((?:0[1-46-9]\d{3})|(?:[1-357-9]\d{4})|(?:[4][0-24-9]\d{3})|(?:[6][013-9]\d{3}))\b",
            "CA" => "^([ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ])\ {0,1}(\d[ABCEGHJKLMNPRSTVWXYZ]\d)$",
            "FR" => "^(F-)?((2[A|B])|[0-9]{2})[0-9]{3}$",
            "IT" => "^(V-|I-)?[0-9]{5}$",
            "AU" => "^(0[289][0-9]{2})|([1345689][0-9]{3})|(2[0-8][0-9]{2})|(290[0-9])|(291[0-4])|(7[0-4][0-9]{2})|(7[8-9][0-9]{2})$",
            "NL" => "^[1-9][0-9]{3}\s?([a-zA-Z]{2})?$",
            "ES" => "^([1-9]{2}|[0-9][1-9]|[1-9][0-9])[0-9]{3}$",
            "DK" => "^([D-d][K-k])?( |-)?[1-9]{1}[0-9]{3}$",
            "SE" => "^(s-|S-){0,1}[0-9]{3}\s?[0-9]{2}$",
            "BE" => "^[1-9]{1}[0-9]{3}$"
        );

        if (!isset($ZIPREG[$address->country_id]))
        {

            return true;
        }

        //Validation failed, provided zip/postal code is not valid.
        if (!preg_match("/" . $ZIPREG[$address->country_id] . "/i", $address->zip))
        {
            $this->validationFailures[] = 'Invalid postcode used';
            return false;
        }

//Validation passed, provided zip/postal code is valid.
        return true;
    }

}
