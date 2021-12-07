<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Propaganistas\LaravelPhone\PhoneNumber;
use Twilio\Rest\Client;
use Worksome\VerifyByPhone\Contracts\InformsServiceAfterVerification;
use Worksome\VerifyByPhone\Contracts\PhoneVerificationService;
use Worksome\VerifyByPhone\Contracts\VerificationCodeManager;
use Worksome\VerifyByPhone\Events\PhoneNumberVerified;
use Worksome\VerifyByPhone\Exceptions\FailedSendingVerificationCodeException;
use Worksome\VerifyByPhone\Exceptions\UnsupportedNumberException;
use Worksome\VerifyByPhone\Exceptions\VerificationCodeExpiredException;
use Worksome\VerifyByPhone\Services\Twilio\TwilioVerificationService;
use Worksome\VerifyByPhone\Tests\Concerns\FakesTwilioRequests;

uses(FakesTwilioRequests::class);

beforeEach(function () {
    $this->service = new TwilioVerificationService(
        $this->app->make(Client::class),
        'aniofandioancdioscnaopdjnaocaejopiof',
        null,
        null,
    );
});

it('can resolve a Twilio client object from the container', function () {
    /** @var Client $client */
    $client = $this->app->make(Client::class);

    // Set in the base TestCase
    expect($client->getAccountSid())->toBe('aniofandioancdioscnaopdjnaocaejopiof');
});

it('can resolve the twilio service via the configured driver', function () {
    config()->set('verify-by-phone.driver', 'twilio');

    expect($this->app->make(PhoneVerificationService::class))
        ->toBeInstanceOf(TwilioVerificationService::class);
});

it('can send a Twilio verification SMS', function () {
    $this->fakeSendRequest('+44 01234567890');

    $this->service->send(new PhoneNumber('+44 01234567890'));

    Http::assertSent(function (Request $request) {
        $url = Str::of($request->url());

        return $url->startsWith('https://verify.twilio.com/v2/Services/')
            && $url->endsWith('/Verifications');
    });
});

it('throws an UnsupportedNumberException if the number is unsupported', function () {
    $this->fakeSendRequestWithError(TwilioVerificationService::ERROR_NUMBER_DOES_NOT_SUPPORT_SMS);

    $this->service->send(new PhoneNumber('+44 01234567890'));
})->throws(UnsupportedNumberException::class);

it('throws a FailedSendingVerificationCodeException if something unknown goes wrong when sending', function () {
    $this->fakeSendRequestWithError(0);

    $this->service->send(new PhoneNumber('+44 01234567890'));
})->throws(FailedSendingVerificationCodeException::class);

it('throws an exception if the code has expired', function () {
    $this->fakeVerifyRequestWithExpiredCode();

    $this->service->verify(new PhoneNumber('+44 01234567890'), '1234');
})->throws(VerificationCodeExpiredException::class);

it('returns true if the code is acceptable', function () {
    $this->fakeVerifyRequest('+44 01234567890');

    $result = $this->service->verify(new PhoneNumber('+44 01234567890'), '1234');

    expect($result)->toBeTrue();
});

it('returns false if the code is incorrect', function () {
    $this->fakeVerifyRequest('+44 01234567890', false);

    $result = $this->service->verify(new PhoneNumber('+44 01234567890'), '1234');

    expect($result)->toBeFalse();
});

it('will use a local verification code manager if generate_codes_locally is true', function () {
    // We fake this event to ensure the twilio service isn't informed of the update
    Event::fake(PhoneNumberVerified::class);
    $this->fakeSendRequest('+44 01234567890');

    config()->set('verify-by-phone.driver', 'twilio');
    config()->set('verify-by-phone.services.twilio.generate_codes_locally', true);

    $phoneNumber = new PhoneNumber('+44 01234567890');
    $this->partialMock(VerificationCodeManager::class)
        ->shouldReceive('store')->with($phoneNumber)->once()->andReturn('123456')
        ->shouldReceive('retrieve')->with($phoneNumber)->once()->andReturn('123456');

    $service = $this->app->make(PhoneVerificationService::class);

    $service->send($phoneNumber);
    $service->verify($phoneNumber, '123456');

    Http::assertNotSent(fn (Request $request) => Str::contains($request->url(), 'VerificationCheck'));
});

it('will throw an expired exception if generate_codes_locally is true and a local verification code is missing', function () {
    config()->set('verify-by-phone.driver', 'twilio');
    config()->set('verify-by-phone.services.twilio.generate_codes_locally', true);

    $phoneNumber = new PhoneNumber('+44 01234567890');
    $this->partialMock(VerificationCodeManager::class)
        ->shouldReceive('retrieve')->with($phoneNumber)->once()->andReturn(null);

    $service = $this->app->make(PhoneVerificationService::class);

    $service->verify($phoneNumber, '123456');
})->throws(VerificationCodeExpiredException::class);

it('fires an event when a verification code is verified', function (string $phoneNumber, string $code, bool $isVerified) {
    config()->set('verify-by-phone.driver', 'twilio');

    Event::fake(PhoneNumberVerified::class);
    $this->fakeVerifyRequest($phoneNumber, $isVerified);

    $service = $this->app->make(PhoneVerificationService::class);
    $service->verify(new PhoneNumber($phoneNumber), $code);

    Event::assertDispatched(PhoneNumberVerified::class, function (PhoneNumberVerified $event) use ($phoneNumber, $code, $isVerified) {
        return $event->phoneNumber->getRawNumber() === $phoneNumber
            && $event->code === $code
            && $event->isVerified === $isVerified;
    });
})->with([
    ['+44 01234567890', '1234'],
])->with([true, false]);

it('implements the InformsServiceAfterVerification contract', function () {
    expect($this->service)->toBeInstanceOf(InformsServiceAfterVerification::class);
});

it('can inform Twilio that a verification code has been verified', function () {
    $this->fakeUpdateVerificationRequest();

    config()->set('verify-by-phone.driver', 'twilio');
    config()->set('verify-by-phone.services.twilio.generate_codes_locally', true);

    $phoneNumber = new PhoneNumber('+44 01234567890');
    $service = $this->app->make(PhoneVerificationService::class);

    $service->informService($phoneNumber, true);

    Http::assertSentCount(1);
});

it('will not inform twilio if not using local codes', function () {
    Http::fake();

    config()->set('verify-by-phone.driver', 'twilio');
    config()->set('verify-by-phone.services.twilio.generate_codes_locally', false);

    $phoneNumber = new PhoneNumber('+44 01234567890');
    $service = $this->app->make(PhoneVerificationService::class);

    $service->informService($phoneNumber, true);

    Http::assertSentCount(0);
});

it('will not inform twilio if the verification result is false', function () {
    Http::fake();

    config()->set('verify-by-phone.driver', 'twilio');
    config()->set('verify-by-phone.services.twilio.generate_codes_locally', true);

    $phoneNumber = new PhoneNumber('+44 01234567890');
    $service = $this->app->make(PhoneVerificationService::class);

    $service->informService($phoneNumber, false);

    Http::assertSentCount(0);
});
