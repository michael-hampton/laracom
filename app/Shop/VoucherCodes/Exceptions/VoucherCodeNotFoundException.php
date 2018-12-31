<?php

namespace App\Shop\VoucherCodes\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VoucherCodeNotFoundException extends NotFoundHttpException
{

    /**
     * AddressNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('Voucher Code not found.');
    }
}
