<?php

namespace App\Utils;

use Exception;

class ValidationException extends Exception
{
    public function __construct($message, $statusCode = 400)
    {
        parent::__construct($message, $statusCode);
    }
}