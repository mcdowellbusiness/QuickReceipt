<?php

namespace App\Exceptions;

use Exception;

class TeamException extends Exception
{
    protected $statusCode;

    public function __construct($message = "", $statusCode = 500, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
