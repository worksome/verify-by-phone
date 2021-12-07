<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone\VerificationCodeManagers;

use Illuminate\Contracts\Cache\Repository;
use Propaganistas\LaravelPhone\PhoneNumber;
use Worksome\VerifyByPhone\Contracts\VerificationCodeGenerator;
use Worksome\VerifyByPhone\Contracts\VerificationCodeManager;

final class CacheVerificationCodeManager implements VerificationCodeManager
{
    public function __construct(
        private Repository $cache,
        private VerificationCodeGenerator $codeGenerator,
    ) {
    }

    public function store(PhoneNumber $phoneNumber): string
    {
        $code = $this->codeGenerator->generate($phoneNumber);
        $this->cache->put($this->getCacheKey($phoneNumber), $code, 60 * 10);

        return $code;
    }

    public function retrieve(PhoneNumber $phoneNumber): null|string
    {
        $result = $this->cache->get($this->getCacheKey($phoneNumber));

        return is_string($result) ? $result : null;
    }

    private function getCacheKey(PhoneNumber $phoneNumber): string
    {
        return "verify-by-phone.verification-code.{$phoneNumber->serialize()}";
    }
}
