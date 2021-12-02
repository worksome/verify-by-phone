<?php

namespace Worksome\VerifyByPhone;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Worksome\VerifyByPhone\Commands\VerifyByPhoneCommand;

class VerifyByPhoneServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('verify-by-phone')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_verify-by-phone_table')
            ->hasCommand(VerifyByPhoneCommand::class);
    }
}
