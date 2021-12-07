<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone\Validation\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Propaganistas\LaravelPhone\PhoneNumber;
use Worksome\VerifyByPhone\Contracts\PhoneVerificationService;

final class VerificationCodeIsValid implements Rule, DataAwareRule
{
    /**
     * @var array<mixed>
     */
    private array $data = [];

    public function __construct(private string|PhoneNumber $phoneNumber)
    {
    }

    public function passes($attribute, $value): bool
    {
        // @phpstan-ignore-next-line
        return app(PhoneVerificationService::class)->verify($this->getPhoneNumber(), strval($value));
    }

    private function getPhoneNumber(): PhoneNumber
    {
        if ($this->phoneNumber instanceof PhoneNumber) {
            return $this->phoneNumber;
        }

        if (!array_key_exists($this->phoneNumber, $this->data)) {
            return new PhoneNumber($this->phoneNumber);
        }

        return new PhoneNumber($this->data[$this->phoneNumber]);
    }

    public function message(): string
    {
        return strval(__('The given verification code is invalid.'));
    }

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }
}
