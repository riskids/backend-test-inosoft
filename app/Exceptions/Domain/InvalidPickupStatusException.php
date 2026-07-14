<?php

namespace App\Exceptions\Domain;

use Exception;

class InvalidPickupStatusException extends Exception
{
    protected $message = 'Invalid pickup status.';
    protected $code = 409;
}
