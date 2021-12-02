<?php

namespace Worksome\VerifyByPhone\Exceptions;

use Exception;
use Throwable;

final class VerificationCodeExpiredException extends Exception
{
    public function __construct(Throwable $previous)
    {
        parent::__construct("The verification code has expired, request a new one.", 0, $previous);
    }

    public static function fromException(Throwable $exception): self
    {
        return new self($exception);
    }
}
