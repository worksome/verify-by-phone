<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone\Contracts;

use Propaganistas\LaravelPhone\PhoneNumber;
use Worksome\VerifyByPhone\Exceptions\FailedSendingVerificationCodeException;
use Worksome\VerifyByPhone\Exceptions\UnknownVerificationErrorException;
use Worksome\VerifyByPhone\Exceptions\UnsupportedNumberException;
use Worksome\VerifyByPhone\Exceptions\VerificationCodeExpiredException;

interface PhoneVerificationService
{
    /**
     * @throws UnsupportedNumberException
     * @throws FailedSendingVerificationCodeException
     */
    public function send(PhoneNumber $number, string $channel = 'sms'): void;

    /**
     * @throws VerificationCodeExpiredException
     * @throws UnknownVerificationErrorException
     */
    public function verify(PhoneNumber $number, string $code): bool;
}
