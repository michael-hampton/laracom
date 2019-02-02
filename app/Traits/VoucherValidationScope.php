<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Traits;

use App\Shop\Vouchers\Voucher;

/**
 * Description of VoucherValidationScope
 *
 * @author michael.hampton
 */
trait VoucherValidationScope {

    /**
     * 
     * @param VoucherRepository $voucherRepo
     * @param VoucherCode $objVoucherCode
     * @param type $cartProducts
     * @return boolean
     */
    protected function validateVoucherScopes(Voucher $objVoucher, $cartProducts) {

        $scopeType = $objVoucher->scope_type;

        foreach ($cartProducts as $cartProduct) {

            switch ($scopeType) {

                case 'Brand':
                    if (empty($cartProduct->product->brand_id)) {

                        return false;
                    }

                    $scopeValue = (int) $objVoucher->scope_value;

                    if ((int) $cartProduct->product->brand_id !== $scopeValue) {

                        return false;
                    }

                    break;

                case 'Product':

                    if (empty($cartProduct->product->id)) {

                        return false;
                    }

                    $scopeValues = explode(',', $objVoucher->scope_value);
                    $blFound = false;

                    foreach ($scopeValues as $scopeValue) {

                        if ($cartProduct->product->id !== (int) $scopeValue) {

                            continue;
                        }

                        $blFound = true;
                    }

                    return $blFound;

                    break;

                case 'Category':

                    $categoryIds = $cartProduct->product->categories()->pluck('category_id')->all();

                    $scopeValue = (int) $objVoucher->scope_value;

                    if (!in_array($scopeValue, $categoryIds)) {

                        return false;
                    }
            }
        }

        return true;
    }

}
