<?php

namespace Worksome\VerifyByPhone\Commands;

use Illuminate\Console\Command;

class VerifyByPhoneCommand extends Command
{
    public $signature = 'verify-by-phone';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
