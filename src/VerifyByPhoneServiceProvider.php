<?php

namespace Worksome\VerifyByPhone;

use Illuminate\Foundation\Application;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Twilio\Rest\Client;
use Worksome\VerifyByPhone\Commands\SendVerificationCodeCommand;
use Worksome\VerifyByPhone\Commands\VerifyVerificationCodeCommand;
use Worksome\VerifyByPhone\Contracts\PhoneVerificationService;
use Worksome\VerifyByPhone\Services\Twilio\TwilioHttpClient;

class VerifyByPhoneServiceProvider extends PackageServiceProvider
{
    public function registeringPackage(): void
    {
        $this->app->singletonIf(Client::class, static function (Application $app) {
            return new Client(
                username: strval($app['config']->get('verify-by-phone.services.twilio.account_sid', '')),
                password: strval($app['config']->get('verify-by-phone.services.twilio.auth_token', '')),
                httpClient: new TwilioHttpClient(),
            );
        });

        $this->app->singleton(PhoneVerificationService::class, static function (Application $app) {
            /** @var VerifyByPhoneManager $manager */
            $manager = $app->make(VerifyByPhoneManager::class);

            return $manager->driver();
        });
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('verify-by-phone')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommands(
                SendVerificationCodeCommand::class,
                VerifyVerificationCodeCommand::class,
            );
    }
}
