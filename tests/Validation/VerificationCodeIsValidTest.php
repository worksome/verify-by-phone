<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\PhoneNumber;
use Worksome\VerifyByPhone\Contracts\PhoneVerificationService;
use Worksome\VerifyByPhone\Services\FakeVerificationService;
use Worksome\VerifyByPhone\Validation\Rules\VerificationCodeIsValid;

beforeEach(function () {
    $this->service = new FakeVerificationService();
    $this->swap(PhoneVerificationService::class, $this->service);
});

it('can verify a validation code', function (mixed $phoneNumber, bool $passes) {
    $this->service->verifyUsing($passes);

    $validator = Validator::make([
        'number' => '+44 01234567890',
        'code' => '1234',
    ], [
        'code' => [
            // Using macro
            Rule::verificationCodeIsValid($phoneNumber),
            // Manually
            new VerificationCodeIsValid($phoneNumber)
        ]
    ]);

    expect($validator->passes())->toBe($passes);
})->with([
    [new PhoneNumber('+44 01234567890')],
    ['+44 01234567890'],
    ['number'],
])->with([true, false]);

it('returns the correct message if the code expired', function () {
    $this->service->actAsThoughTheVerificationCodeExpired();

    $validator = Validator::make(['code' => '1234'], [
        'code' => [new VerificationCodeIsValid('+44 01234567890')]
    ]);

    expect($validator->errors()->get('code')[0])->toBe(
        'The given verification code has expired. Please request a new one.'
    );
});
