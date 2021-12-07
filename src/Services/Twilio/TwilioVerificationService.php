<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone\Services\Twilio;

use Exception;
use Propaganistas\LaravelPhone\PhoneNumber;
use Throwable;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Twilio\Rest\Verify\V2\Service\VerificationCheckList;
use Twilio\Rest\Verify\V2\Service\VerificationList;
use Worksome\VerifyByPhone\Contracts\PhoneVerificationService;
use Worksome\VerifyByPhone\Contracts\VerificationCodeManager;
use Worksome\VerifyByPhone\Exceptions\FailedSendingVerificationCodeException;
use Worksome\VerifyByPhone\Exceptions\UnknownVerificationErrorException;
use Worksome\VerifyByPhone\Exceptions\UnsupportedNumberException;
use Worksome\VerifyByPhone\Exceptions\VerificationCodeExpiredException;

/**
 * @internal
 */
final class TwilioVerificationService implements PhoneVerificationService
{
    public const ERROR_NUMBER_DOES_NOT_SUPPORT_SMS = 60205;
    public const ERROR_NOT_FOUND = 20404;

    private VerificationList $verifications;
    private VerificationCheckList $verificationChecks;

    public function __construct(
        Client $twilio,
        string $verifyId,
        private ?VerificationCodeManager $verificationCodeManager,
    ) {
        $twilio = $twilio->verify->v2->services($verifyId);
        $this->verifications = $twilio->verifications;
        $this->verificationChecks = $twilio->verificationChecks;
    }

    public function send(PhoneNumber $number): void
    {
        try {
            $this->verifications->create($number->formatE164(), 'sms', $this->getSendOptions($number));
        } catch (TwilioException $e) {
            throw match ($e->getCode()) {
                self::ERROR_NUMBER_DOES_NOT_SUPPORT_SMS => UnsupportedNumberException::fromException($e),
                default => FailedSendingVerificationCodeException::fromException($e),
            };
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getSendOptions(PhoneNumber $number): array
    {
        $options = [];

        if ($this->isUsingLocallyGeneratedCodes()) {
            $options['customCode'] = $this->verificationCodeManager?->store($number);
        }

        return $options;
    }

    public function verify(PhoneNumber $number, string $code): bool
    {
        try {
            return $this->codeIsValid($number, $code);
        } catch (Throwable $e) {
            throw match ($e->getCode()) {
                self::ERROR_NOT_FOUND => VerificationCodeExpiredException::fromException($e),
                default => UnknownVerificationErrorException::fromException($e),
            };
        }
    }

    /**
     * @throws TwilioException
     * @throws Exception
     */
    private function codeIsValid(PhoneNumber $number, string $code): bool
    {
        if (! $this->isUsingLocallyGeneratedCodes()) {
            return $this->verificationChecks->create($code, ['to' => $number->formatE164()])->status === 'approved';
        }

        $storedCode = $this->verificationCodeManager?->retrieve($number);

        throw_unless($storedCode, new Exception('The verification code is not stored locally', self::ERROR_NOT_FOUND));

        return $code === $storedCode;
    }

    private function isUsingLocallyGeneratedCodes(): bool
    {
        return $this->verificationCodeManager !== null;
    }
}
