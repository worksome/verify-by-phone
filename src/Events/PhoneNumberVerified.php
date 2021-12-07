<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone\Events;

use Propaganistas\LaravelPhone\PhoneNumber;

final class PhoneNumberVerified
{
    public function __construct(
        public PhoneNumber $phoneNumber,
        public string $code,
        public bool $isVerified,
    ) {
    }
}
