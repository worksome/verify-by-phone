# Verify your users by call or SMS

[![PHPStan](https://github.com/worksome/verify-by-phone/actions/workflows/phpstan.yml/badge.svg)](https://github.com/worksome/verify-by-phone/actions/workflows/phpstan.yml)
[![run-tests](https://github.com/worksome/verify-by-phone/actions/workflows/run-tests.yml/badge.svg)](https://github.com/worksome/verify-by-phone/actions/workflows/run-tests.yml)

It's a common practice: a user signs up, you send an SMS to their phone with a code, they enter that code in your
application and they're off to the races.

This package makes it simple to add this capability to your Laravel application.

## Installation

You can install the package via composer:

```bash
composer require worksome/verify-by-phone
```

You can publish the config file by running:

```bash
php artisan vendor:publish --tag="verify-by-phone-config"
```

## Configuration

This package is built to support multiple verification services. The primary service
is [Twilio](https://www.twilio.com/). You may configure the service in the config file at `config/verify-by-phone.php`
under `driver`, or by using the dedicated `.env` variable: `VERIFY_BY_PHONE_DRIVER`.

### `twilio`

To use our Twilio integration, you'll need to provide an `account_sid`, `auth_token` and `verify_sid`. All of these can
be set in the `config/verify-by-phone.php` file under `services.twilio`.

## Usage

To use this package, you'll want to inject the `\Worksome\VerifyByPhone\Contracts\PhoneVerificationService`
contract into your application. Let's imagine that you want to send the verification code in a controller method:

```php
public function sendVerificationCode(Request $request, PhoneVerificationService $verificationService)
{
    // Send a verification code to the given number
    $verificationService->send(new PhoneNumber($request->input('phone')));
    
    return redirect(route('home'))->with('message', 'Verification code sent!');
}
```

It's as simple as that! Note that we are using `\Propaganistas\LaravelPhone\PhoneNumber` to safely parse numbers in
different formats.

Now, when a user receives their verification code, you'll want to check that it is valid. Use the `verify` method for
this:

```php
public function verifyCode(Request $request, PhoneVerificationService $verificationService)
{
    // Verify the verification code for the given phone number
    $valid = $verificationService->verify(
        new PhoneNumber($request->input('phone')), 
        $request->input('code')
    );
    
    if ($valid) {
        // Mark your user as valid
    }
}
```

The first parameter is the phone number (again using `\Propaganistas\LaravelPhone\PhoneNumber`), and the second is the
verification code provided by the user.

## Validation rule

We offer a rule to make it easy to verify a verification code during validation.

> Be aware that this rule will call the `verify` method of the `PhoneVerificationService` contract, and likely will
> make an HTTP request.

```php
Validator::validate($request->all(), [
    'phone_number' => ['required'],
    'verification_code' => ['required', Rule::verificationCodeIsValid('phone_number')],
]);
```

If your data doesn't include the phone number, you may instead pass it in manually:

```php
Validator::validate($request->all(), [
    'verification_code' => ['required', Rule::verificationCodeIsValid('+441174960123')],
]);
```

We extend the `Rule` class for visual consistency with other rules, but you can also use the `VerificationCodeIsValid` rule directly for
better IDE support:

```php
Validator::validate($request->all(), [
    'verification_code' => ['required', new VerificationCodeIsValid('+441174960123')],
]);
```

This rule will also handle the case where the verification code has expired and return a suitable error message.

## Artisan commands

This package ships with a couple of artisan commands that allow you to send and verify verification codes.

```bash
# Send a verication code to the given phone number
php artisan verify-by-phone:send "+441174960123"

# Check that a given verication code is valid for the given phone number
php artisan verify-by-phone:verify "+441174960123" 1234
```

The verify command will return a console failure if verification fails.

## Testing

When writing tests, you likely do not want to make real requests to services such as Twilio. To support testing, we
provide a
`FakeVerificationService` that can be used to mock the verification service. To use it, you should set an `env` variable
in your `phpunit.xml` with the following value:

```xml

<env name='VERIFY_BY_PHONE_DRIVER' value='null'/>
```

Alternatively, you may manually swap out the integration in your test via using the `swap` method. The fake
implementation has some useful testing methods you can use:

```php
it('tests something to do with SMS verification', function () {
    $service = new FakeVerificationService();
    $this->swap(PhoneVerificationService::class, $service);
   
    // Customise what happens when calling `send`
    $service->sendUsing(fn () => throw new Exception('Something went wrong'));
    
    // Customise what happens when calling `verify`
    $service->verifyUsing(fn () => throw new Exception('Something went wrong'));
    
    // Throw a VerificationCodeExpiredException
    $service->actAsThoughTheVerificationCodeExpired();
    
    // Assert that a verification was "sent" on the given number
    $service->assertSentVerification(new PhoneNumber('+441174960123'));
});
```

You may execute this project's tests by running:

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Luke Downing](https://github.com/lukeraymonddowning)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
