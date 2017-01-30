<?php
namespace App\Exceptions;

use Exception;

class UserException extends \RuntimeException
{
    public function __construct($message, $code = -1, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}