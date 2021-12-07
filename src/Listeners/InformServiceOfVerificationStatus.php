<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Worksome\VerifyByPhone\Contracts\InformsServiceAfterVerification;
use Worksome\VerifyByPhone\Contracts\PhoneVerificationService;
use Worksome\VerifyByPhone\Events\PhoneNumberVerified;

/**
 * @internal
 */
final class InformServiceOfVerificationStatus implements ShouldQueue
{
    public int $tries = 5;

    public function __construct(private PhoneVerificationService $verificationService)
    {
    }

    public function handle(PhoneNumberVerified $event): void
    {
        /** @var InformsServiceAfterVerification $service */
        $service = $this->verificationService;

        $service->informService($event->phoneNumber, $event->isVerified);
    }

    public function shouldQueue(PhoneNumberVerified $event): bool
    {
        return $this->verificationService instanceof InformsServiceAfterVerification;
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [1, 5, 10, 60, 60];
    }
}
