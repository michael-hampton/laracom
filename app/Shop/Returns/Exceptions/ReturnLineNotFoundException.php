<?php
namespace App\Shop\Returns\Exceptions;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
class ReturnLineNotFoundException extends NotFoundHttpException
{
    /**
     * ReturnLineNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('Return not found.');
    }
}
