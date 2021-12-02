<?php

namespace Worksome\VerifyByPhone\Commands;

use Illuminate\Console\Command;
use Propaganistas\LaravelPhone\PhoneNumber;
use Worksome\VerifyByPhone\Contracts\PhoneVerificationService;

class SendVerificationCodeCommand extends Command
{
    public $signature = 'verify-by-phone:send {number?}';

    public $description = 'Send a verification code to the given phone number';

    public function handle(PhoneVerificationService $verificationService): int
    {
        $number = strval($this->argument('number') ?? $this->ask('Number:'));

        $verificationService->send(new PhoneNumber($number));

        return self::SUCCESS;
    }
}
