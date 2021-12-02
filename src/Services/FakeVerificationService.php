<?php

namespace Worksome\VerifyByPhone\Services;

use Closure;
use PHPUnit\Framework\Assert;
use Propaganistas\LaravelPhone\PhoneNumber;
use Worksome\VerifyByPhone\Contracts\PhoneVerificationService;

final class FakeVerificationService implements PhoneVerificationService
{
    /**
     * @var array<int, string>
     */
    private array $verifications = [];

    /**
     * @var null|Closure(PhoneNumber, string): void
     */
    private null|Closure $sendUsing = null;

    /**
     * @var bool|Closure(PhoneNumber, string): bool
     */
    private bool|Closure $verifyUsing = true;

    public function send(PhoneNumber $number): void
    {
        $this->verifications[] = $number->formatE164();

        if ($this->sendUsing) {
            $this->sendUsing->__invoke($number);
        }
    }

    public function verify(PhoneNumber $number, string $code): bool
    {
        if ($this->verifyUsing instanceof Closure) {
            // @phpstan-ignore-next-line
            return $this->verifyUsing->__invoke($number, $code);
        }

        return $this->verifyUsing;
    }

    /**
     * @param Closure(PhoneNumber): void $result
     */
    public function sendUsing(Closure $result): self
    {
        $this->sendUsing = $result;

        return $this;
    }

    /**
     * @param bool|Closure(PhoneNumber, string): bool $result
     */
    public function verifyUsing(bool|Closure $result): self
    {
        $this->verifyUsing = $result;

        return $this;
    }

    /**
     * Assert that the given phone number has been sent a verification.
     */
    public function assertSentVerification(PhoneNumber $number): self
    {
        Assert::assertContains($number->formatE164(), $this->verifications);

        return $this;
    }
}
