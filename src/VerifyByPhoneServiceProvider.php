<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\Rule;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Twilio\Rest\Client;
use Worksome\VerifyByPhone\Commands\SendVerificationCodeCommand;
use Worksome\VerifyByPhone\Commands\VerifyVerificationCodeCommand;
use Worksome\VerifyByPhone\Contracts\PhoneVerificationService;
use Worksome\VerifyByPhone\Contracts\VerificationCodeGenerator;
use Worksome\VerifyByPhone\Contracts\VerificationCodeManager;
use Worksome\VerifyByPhone\Events\PhoneNumberVerified;
use Worksome\VerifyByPhone\Listeners\InformServiceOfVerificationStatus;
use Worksome\VerifyByPhone\Services\Twilio\TwilioHttpClient;
use Worksome\VerifyByPhone\Validation\Rules\VerificationCodeIsValid;
use Worksome\VerifyByPhone\VerificationCodeGenerators\NumericVerificationCodeGenerator;
use Worksome\VerifyByPhone\VerificationCodeManagers\CacheVerificationCodeManager;

/**
 * @internal
 */
class VerifyByPhoneServiceProvider extends PackageServiceProvider
{
    public function registeringPackage(): void
    {
        $this->app->singleton(PhoneVerificationService::class, static function (Application $app) {
            /** @var VerifyByPhoneManager $manager */
            $manager = $app->make(VerifyByPhoneManager::class);

            return $manager->driver();
        });

        $this->app->singleton(VerificationCodeGenerator::class, NumericVerificationCodeGenerator::class);
        $this->app->singleton(VerificationCodeManager::class, CacheVerificationCodeManager::class);
    }

    public function bootingPackage(): void
    {
        // We register in the boot method to allow a user to provide their own client if using the Twilio SDK.
        $this->app->singletonIf(Client::class, static function (Application $app) {
            return new Client(
                username: strval($app['config']->get('verify-by-phone.services.twilio.account_sid', '')),
                password: strval($app['config']->get('verify-by-phone.services.twilio.auth_token', '')),
                httpClient: new TwilioHttpClient(),
            );
        });

        Rule::macro('verificationCodeIsValid', fn (string $field) => new VerificationCodeIsValid($field));

        Event::listen(PhoneNumberVerified::class, InformServiceOfVerificationStatus::class);
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('verify-by-phone')
            ->hasConfigFile()
            ->hasCommands(
                SendVerificationCodeCommand::class,
                VerifyVerificationCodeCommand::class,
            );
    }
}
