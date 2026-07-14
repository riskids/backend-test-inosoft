<?php

namespace App\Exceptions\Domain;

use Exception;

class SafetyCheckRequiredException extends Exception
{
    protected $message = 'Safety check required before scheduling.';
    protected $code = 422;
}
