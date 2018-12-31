<?php

namespace App\Shop\Channels\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChannelNotFoundException extends NotFoundHttpException {

    /**
     * ChannelNotFoundException constructor.
     */
    public function __construct() {
        parent::__construct('Channel not found.');
    }

}
