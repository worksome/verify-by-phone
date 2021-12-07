<?php

use Illuminate\Support\Facades\Event;
use Worksome\VerifyByPhone\Events\PhoneNumberVerified;
use Worksome\VerifyByPhone\Listeners\InformServiceOfVerificationStatus;

beforeEach(function () {
    $this->listener = $this->app->make(InformServiceOfVerificationStatus::class);
});

it('listens to the PhoneNumberVerified event', function () {
    Event::fake();

    Event::assertListening(PhoneNumberVerified::class, InformServiceOfVerificationStatus::class);
});

it('provides an exponential backoff', function () {
    expect($this->listener->backoff())->toBe([1, 5, 10, 60, 60]);
});
