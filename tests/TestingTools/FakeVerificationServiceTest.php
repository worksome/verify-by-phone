<?php

use Propaganistas\LaravelPhone\PhoneNumber;
use Worksome\VerifyByPhone\Exceptions\VerificationCodeExpiredException;
use Worksome\VerifyByPhone\Services\FakeVerificationService;

beforeEach(function () {
    $this->service = new FakeVerificationService();
});

it('can declare an action to perform when sending', function () {
    $this->service->sendUsing(fn () => throw new Exception('It worked!'));

    $this->service->send(new PhoneNumber('+44 01234567890'));
})->throws('It worked!');

it('can declare an action to perform when verifying', function () {
    $this->service->verifyUsing(fn () => throw new Exception('It worked!'));

    $this->service->verify(new PhoneNumber('+44 01234567890'), '1234');
})->throws('It worked!');

it('can pretend that the verification code expired', function () {
    $this->service->actAsThoughTheVerificationCodeExpired();

    $this->service->verify(new PhoneNumber('+44 01234567890'), '1234');
})->throws(VerificationCodeExpiredException::class);
