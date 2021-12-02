<?php

use Propaganistas\LaravelPhone\PhoneNumber;
use Worksome\VerifyByPhone\Contracts\PhoneVerificationService;
use Worksome\VerifyByPhone\Services\FakeVerificationService;

beforeEach(function () {
    $this->service = new FakeVerificationService();
    $this->app->instance(PhoneVerificationService::class, $this->service);
});

it('sends a verification code for the given number', function () {
    $this->artisan('verify-by-phone:send +4401234567890')
        ->assertSuccessful();

    $this->service->assertSentVerification(new PhoneNumber('+4401234567890'));
});

it('asks for the phone number if not given', function () {
    $this->artisan('verify-by-phone:send')
        ->expectsQuestion('Number:', '+4401234567890')
        ->assertSuccessful();

    $this->service->assertSentVerification(new PhoneNumber('+4401234567890'));
});
