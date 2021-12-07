<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone\Exceptions;

use Exception;
use Throwable;

final class UnknownVerificationErrorException extends Exception
{
    public function __construct(Throwable $previous)
    {
        parent::__construct("Something went wrong while verifying sms code.", 0, $previous);
    }

    public static function fromException(Throwable $exception): self
    {
        return new self($exception);
    }
}
