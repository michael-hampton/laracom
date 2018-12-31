<?php

namespace App\Shop\Refunds\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RefundNotFoundException extends NotFoundHttpException
{

    /**
     * AddressNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('Refund not found.');
    }
}
