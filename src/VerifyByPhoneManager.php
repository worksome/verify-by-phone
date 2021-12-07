<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone;

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
        /** @var Client $client */
        $client = $this->container->make(Client::class);
        /** @var VerificationCodeManager $verificationCodeManager */
        $verificationCodeManager = $this->container->make(VerificationCodeManager::class);

        $generateCodeLocally = boolval($this->config->get('verify-by-phone.services.twilio.generate_codes_locally', false));

        return new TwilioVerificationService(
            $client,
            strval($this->config->get('verify-by-phone.services.twilio.verify_sid')),
            $generateCodeLocally ? $verificationCodeManager : null
        );
    }
}
