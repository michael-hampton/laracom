<?php

namespace App\Shop\OrderProducts\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderProductNotFoundException extends NotFoundHttpException {

    /**
     * ChannelNotFoundException constructor.
     */
    public function __construct() {
        parent::__construct('Order line not found.');
    }

}
