<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone\Exceptions;

use Exception;
use Throwable;

final class UnsupportedNumberException extends Exception
{
    public function __construct(Throwable $previous)
    {
        parent::__construct('Landline is not supported', 0, $previous);
    }

    public static function fromException(Exception $exception): self
    {
        return new self($exception);
    }
}
