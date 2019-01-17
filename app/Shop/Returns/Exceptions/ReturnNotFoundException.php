<?php
namespace App\Shop\Returns\Exceptions;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
class ReturnNotFoundException extends NotFoundHttpException
{
    /**
     * ReturnNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('Return not found.');
    }
}
