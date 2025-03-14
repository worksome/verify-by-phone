<?php

namespace Worksome\VerifyByPhone\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as Orchestra;
use Worksome\VerifyByPhone\VerifyByPhoneServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Worksome\\VerifyByPhone\\Database\\Factories\\' . class_basename(
                $modelName
            ) . 'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            VerifyByPhoneServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        // Create fake twilio credentials
        config()->set('verify-by-phone.services.twilio.account_sid', 'aniofandioancdioscnaopdjnaocaejopiof');
        config()->set('verify-by-phone.services.twilio.auth_token', 'naofnoapnwapodnwapodnawofpnawodnaw');
        config()->set('verify-by-phone.services.twilio.verify_sid', 'aniofandioancdioscnaopdjnaocaejopiof');

        Http::preventStrayRequests();
    }
}
