<?php

namespace Worksome\VerifyByPhone\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Worksome\VerifyByPhone\VerifyByPhone
 */
class VerifyByPhone extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'verify-by-phone';
    }
}
