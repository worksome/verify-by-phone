<?php

use Worksome\VerifyByPhone\Contracts\PhoneVerificationService;
use Worksome\VerifyByPhone\Services\FakeVerificationService;

beforeEach(function () {
    $this->service = new FakeVerificationService();
    $this->app->instance(PhoneVerificationService::class, $this->service);
});

it('verifies a verification code for the given number', function () {
    $this->artisan('verify-by-phone:verify +4401234567890 1234')
        ->assertSuccessful();
});

it('asks for the phone number and code if not given', function () {
    $this->artisan('verify-by-phone:verify')
        ->expectsQuestion('Number:', '+4401234567890')
        ->expectsQuestion('Code:', '1234')
        ->assertSuccessful();
});

it('fails if verification fails', function () {
    $this->service->verifyUsing(false);

    $this->artisan('verify-by-phone:verify +4401234567890 1234')
        ->assertFailed();
});
