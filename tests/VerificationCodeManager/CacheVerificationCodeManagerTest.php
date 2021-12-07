<?php

use Propaganistas\LaravelPhone\PhoneNumber;
use Worksome\VerifyByPhone\Contracts\VerificationCodeManager;
use Worksome\VerifyByPhone\VerificationCodeGenerators\NumericVerificationCodeGenerator;
use Worksome\VerifyByPhone\VerificationCodeManagers\CacheVerificationCodeManager;

it('is the default implementation', function () {
    expect($this->app->make(VerificationCodeManager::class))
        ->toBeInstanceOf(CacheVerificationCodeManager::class);
});

it('can store and retrieve the verification code', function () {
    $manager = $this->app->make(CacheVerificationCodeManager::class);

    $generatedCode = $manager->store(new PhoneNumber('+44 01234567890'));
    $retrievedCode = $manager->retrieve(new PhoneNumber('+44 01234567890'));

    expect($retrievedCode)->toBe($generatedCode);
});

it('expires after 5 minutes', function () {
    $manager = new CacheVerificationCodeManager(
        $this->app->make('cache.store'),
        new NumericVerificationCodeGenerator(),
    );

    $manager->store(new PhoneNumber('+44 01234567890'));
    $this->travel(60 * 5 + 1)->seconds();
    $code = $manager->retrieve(new PhoneNumber('+44 01234567890'));

    expect($code)->toBeNull();
});
