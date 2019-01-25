<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Traits;

use App\Shop\VoucherCodes\VoucherCode;

/**
 * Description of VoucherValidationScope
 *
 * @author michael.hampton
 */
trait VoucherValidationScope {

    /**
     * 
     * @param type $results
     * @param type $cartProducts
     * @return boolean
     */
    protected function validateVoucherScopes(VoucherCode $objVoucherCode, $cartProducts) {
        
        $scopeType = $objVoucherCode->scope_type;
        $scopeValue = (int) $objVoucherCode->scope_value;

        foreach ($cartProducts as $cartProduct) {

            switch ($scopeType) {

                case 'Brand':
                    if (empty($cartProduct->product->brand_id)) {

                        return false;
                    }

                    if ((int) $cartProduct->product->brand_id !== $scopeValue) {

                        return false;
                    }

                    break;

                case 'Product':

                    if (empty($cartProduct->product->id)) {

                        return false;
                    }

                    if ((int) $cartProduct->product->id !== $scopeValue) {

                        return false;
                    }

                    break;

                case 'Category':

                    $categoryIds = $cartProduct->product->categories()->pluck('category_id')->all();

                    if (!in_array($scopeValue, $categoryIds)) {

                        return false;
                    }
            }
        }

        return true;
    }

}
