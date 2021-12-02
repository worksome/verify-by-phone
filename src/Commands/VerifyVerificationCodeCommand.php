<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone\Commands;

use Illuminate\Console\Command;
use Propaganistas\LaravelPhone\PhoneNumber;
use Worksome\VerifyByPhone\Contracts\PhoneVerificationService;

/**
 * @internal
 */
class VerifyVerificationCodeCommand extends Command
{
    public $signature = 'verify-by-phone:verify {number?} {code?}';

    public $description = 'Verify the given verification code for the given phone number';

    public function handle(PhoneVerificationService $verificationService): int
    {
        $number = strval($this->argument('number') ?? $this->ask('Number:'));
        $code = strval($this->argument('code') ?? $this->ask('Code:'));

        $result = $verificationService->verify(new PhoneNumber($number), $code);

        if (! $result) {
            $this->error('Incorrect verification code!');

            return self::FAILURE;
        }

        $this->line('Verification successful!');

        return self::SUCCESS;
    }
}
