<?php
namespace App\Shop\CourierRates\Exceptions;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
class CourierRateNotFoundException extends NotFoundHttpException
{
    /**
     * CourierRateNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('Courier rate not found.');
    }
}
