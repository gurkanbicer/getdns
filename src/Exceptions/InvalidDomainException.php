<?php

namespace Gurkanbicer\Getdns\Exceptions;

use Exception;

class InvalidDomainException extends Exception
{
    public function __construct($message = "Invalid domain name.", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}