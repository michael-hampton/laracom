<?php

namespace App\Shop\Vouchers\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VoucherNotFoundException extends NotFoundHttpException
{

    /**
     * AddressNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('Voucher not found.');
    }
}
