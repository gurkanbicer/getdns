<?php

namespace Gurkanbicer\Getdns\Exceptions;

use Exception;

class InvalidQueryTypeException extends Exception
{
    public function __construct($message = "Invalid query type.", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}