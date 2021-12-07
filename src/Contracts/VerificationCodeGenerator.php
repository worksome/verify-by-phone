<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone\Contracts;

use Propaganistas\LaravelPhone\PhoneNumber;

interface VerificationCodeGenerator
{
    /**
     * Generate a verification code that will be sent to the user.
     */
    public function generate(PhoneNumber $phoneNumber): string;
}
