<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone\VerificationCodeGenerators;

use Illuminate\Support\Collection;
use Propaganistas\LaravelPhone\PhoneNumber;
use Worksome\VerifyByPhone\Contracts\VerificationCodeGenerator;

final class NumericVerificationCodeGenerator implements VerificationCodeGenerator
{
    private const CODE_LENGTH = 6;

    public function generate(PhoneNumber $phoneNumber): string
    {
        return Collection::times(self::CODE_LENGTH, fn () => mt_rand(0, 9))->join('');
    }
}
