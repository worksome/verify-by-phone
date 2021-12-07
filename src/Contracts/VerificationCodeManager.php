<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone\Contracts;

use Propaganistas\LaravelPhone\PhoneNumber;

interface VerificationCodeManager
{
    /**
     * Store a new verification code for the given phone number.
     */
    public function store(PhoneNumber $phoneNumber): string;

    /**
     * Retrieve the stored verification code for the given phone number.
     */
    public function retrieve(PhoneNumber $phoneNumber): null|string;
}
