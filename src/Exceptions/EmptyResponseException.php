<?php

namespace Gurkanbicer\Getdns\Exceptions;

use Exception;

class EmptyResponseException extends Exception
{
    public function __construct($message = "The command did not give any response.", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}