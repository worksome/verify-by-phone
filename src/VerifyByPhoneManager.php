<?php

namespace Worksome\VerifyByPhone;

use Illuminate\Support\Manager;
use Twilio\Rest\Client;
use Worksome\VerifyByPhone\Services\FakeVerificationService;
use Worksome\VerifyByPhone\Services\Twilio\TwilioVerificationService;

class VerifyByPhoneManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return strval($this->config->get('verify-by-phone.driver', 'null'));
    }

    public function createNullDriver(): FakeVerificationService
    {
        return new FakeVerificationService();
    }

    public function createTwilioDriver(): TwilioVerificationService
    {
        /** @var Client $client */
        $client = $this->container->make(Client::class);

        return new TwilioVerificationService(
            $client,
            strval($this->config->get('verify-by-phone.services.twilio.verify_sid')),
        );
    }
}
