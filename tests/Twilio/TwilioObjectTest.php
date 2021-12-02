<?php

use Twilio\Rest\Client;
use Worksome\VerifyByPhone\Contracts\PhoneVerificationService;
use Worksome\VerifyByPhone\Services\Twilio\TwilioVerificationService;

it('can resolve a Twilio client object from the container', function () {
    /** @var Client $client */
    $client = $this->app->make(Client::class);

    // Set in the base TestCase
    expect($client->getAccountSid())->toBe('AC123');
});

it('can resolve the twilio service via the configured driver', function () {
    config()->set('verify-by-phone.driver', 'twilio');

    expect($this->app->make(PhoneVerificationService::class))
        ->toBeInstanceOf(TwilioVerificationService::class);
});
