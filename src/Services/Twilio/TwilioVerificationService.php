<?php

namespace Worksome\VerifyByPhone\Services\Twilio;

use Propaganistas\LaravelPhone\PhoneNumber;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Twilio\Rest\Verify\V2\Service\VerificationCheckList;
use Twilio\Rest\Verify\V2\Service\VerificationList;
use Worksome\VerifyByPhone\Contracts\PhoneVerificationService;
use Worksome\VerifyByPhone\Exceptions\FailedSendingVerificationCodeException;
use Worksome\VerifyByPhone\Exceptions\UnknownVerificationErrorException;
use Worksome\VerifyByPhone\Exceptions\UnsupportedNumberException;
use Worksome\VerifyByPhone\Exceptions\VerificationCodeExpiredException;

final class TwilioVerificationService implements PhoneVerificationService
{
    private const ERROR_NUMBER_DOES_NOT_SUPPORT_SMS = 60205;
    private const ERROR_NOT_FOUND = 20404;

    private VerificationList $verifications;
    private VerificationCheckList $verificationChecks;

    public function __construct(Client $twilio, string $verifyId)
    {
        $twilio = $twilio->verify->v2->services($verifyId);
        $this->verifications = $twilio->verifications;
        $this->verificationChecks = $twilio->verificationChecks;
    }

    public function send(PhoneNumber $number): void
    {
        try {
            $this->verifications->create($number->formatE164(), 'sms');
        } catch (TwilioException $e) {
            throw match ($e->getCode()) {
                self::ERROR_NUMBER_DOES_NOT_SUPPORT_SMS => UnsupportedNumberException::fromException($e),
                default => FailedSendingVerificationCodeException::fromException($e),
            };
        }
    }

    public function verify(PhoneNumber $number, string $code): bool
    {
        try {
            $response = $this->verificationChecks->create(
                $code,
                ['to' => $number->formatE164()]
            );
        } catch (TwilioException $e) {
            throw match ($e->getCode()) {
                self::ERROR_NOT_FOUND => VerificationCodeExpiredException::fromException($e),
                default => UnknownVerificationErrorException::fromException($e),
            };
        }

        return $response->status === 'approved';
    }
}
