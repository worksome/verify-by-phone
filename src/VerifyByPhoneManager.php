<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Manager;
use Twilio\Rest\Client;
use Worksome\VerifyByPhone\Contracts\VerificationCodeManager;
use Worksome\VerifyByPhone\Services\FakeVerificationService;
use Worksome\VerifyByPhone\Services\Twilio\TwilioVerificationService;

/**
 * @internal
 */
class VerifyByPhoneManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return strval($this->config->get('verify-by-phone.driver') ?? 'null');
    }

    public function createNullDriver(): FakeVerificationService
    {
        return new FakeVerificationService();
    }

    public function createTwilioDriver(): TwilioVerificationService
    {
        $generateCodeLocally = boolval($this->config->get('verify-by-phone.services.twilio.generate_codes_locally', false));

        return new TwilioVerificationService(
            $this->getTwilioClient(),
            strval($this->config->get('verify-by-phone.services.twilio.verify_sid')),
            $generateCodeLocally ? $this->getVerificationCodeManager() : null,
            $this->getEventDispatcher(),
        );
    }

    private function getTwilioClient(): Client
    {
        // @phpstan-ignore-next-line
        return $this->container->make(Client::class);
    }

    private function getVerificationCodeManager(): VerificationCodeManager
    {
        // @phpstan-ignore-next-line
        return $this->container->make(VerificationCodeManager::class);
    }

    private function getEventDispatcher(): Dispatcher
    {
        // @phpstan-ignore-next-line
        return $this->container->make('events');
    }
}
