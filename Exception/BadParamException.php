<?php

namespace Ybenhssaien\EntityEncoderBundle\Exception;

use Throwable;

class BadParamException extends \InvalidArgumentException
{
    public function __construct($message = '', $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
