<?php

use Illuminate\Support\Collection;
use Propaganistas\LaravelPhone\PhoneNumber;
use Worksome\VerifyByPhone\Contracts\VerificationCodeGenerator;
use Worksome\VerifyByPhone\VerificationCodeGenerators\NumericVerificationCodeGenerator;

it('is the default implementation', function () {
    expect($this->app->make(VerificationCodeGenerator::class))
        ->toBeInstanceOf(NumericVerificationCodeGenerator::class);
});

it('generates a random 6 digit numeric code', function () {
    $generator = fn() => (new NumericVerificationCodeGenerator())->generate(new PhoneNumber('+44 01234567890'));

    expect(Collection::times(50, $generator)->unique())
        ->toHaveCount(50)
        ->each->toBeNumeric()->toHaveLength(6);
});
