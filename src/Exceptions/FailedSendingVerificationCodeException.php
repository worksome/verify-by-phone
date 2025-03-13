<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone\Exceptions;

use Exception;
use Throwable;

final class FailedSendingVerificationCodeException extends Exception
{
    public function __construct(Throwable $previous)
    {
        parent::__construct('Failed sending verification code', 0, $previous);
    }

    public static function fromException(Exception $exception): self
    {
        return new self($exception);
    }
}
