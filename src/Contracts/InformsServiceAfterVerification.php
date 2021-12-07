<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone\Contracts;

use Propaganistas\LaravelPhone\PhoneNumber;

interface InformsServiceAfterVerification
{
    public function informService(PhoneNumber $phoneNumber, bool $isVerified): void;
}
