<?php

namespace App\Exceptions\Domain;

use Exception;

class UnpaidPaymentExistsException extends Exception
{
    protected $message = 'Household has an unpaid payment.';
    protected $code = 422;
}
