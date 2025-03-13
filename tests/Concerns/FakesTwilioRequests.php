<?php

namespace Worksome\VerifyByPhone\Tests\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Propaganistas\LaravelPhone\PhoneNumber;
use Twilio\Exceptions\TwilioException;

trait FakesTwilioRequests
{
    public function fakeSendRequest(string $number, array $response = []): void
    {
        $number = new PhoneNumber($number);

        $body = [
            'status' => 'pending',
            'payee' => null,
            'date_updated' => '2021-12-02T13:16:12Z',
            'send_code_attempts' => [
                [
                    'attempt_sid' => 'VL4a2acf6d2fb3ac2a1f141e5510d42244',
                    'channel' => 'sms',
                    'time' => '2021-12-02T13:15:48.000Z',
                ],
                [
                    'attempt_sid' => 'VLfb7b1b241ac18a28c6941d58355dab26',
                    'channel' => 'sms',
                    'time' => '2021-12-02T13:16:12.515Z',
                ],
            ],
            'account_sid' => 'aniofandioancdioscnaopdjnaocaejopiof',
            'to' => $number->formatE164(),
            'amount' => null,
            'valid' => false,
            'lookup' => [
                'carrier' => [
                    'mobile_country_code' => '234',
                    'type' => 'mobile',
                    'error_code' => null,
                    'mobile_network_code' => '15',
                    'name' => 'Foobar',
                ],
            ],
            'url' => 'https://verify.twilio.com/v2/Services/aniofandioancdioscnaopdjnaocaejopiof/Verifications/oapmfopadmaifbnaopxnaionadopnaiocnwdaop',
            'sid' => 'oapmfopadmaifbnaopxnaionadopnaiocnwdaop',
            'date_created' => '2021-12-02T13:15:48Z',
            'service_sid' => 'afoiandopanaopcnawopanmcopamopacoapdn',
            'channel' => 'sms',
        ];

        foreach ($response as $key => $value) {
            $body = Arr::set($body, $key, $value);
        }

        Http::fake(['https://verify.twilio.com/v2/Services/*/Verifications' => $body]);
    }

    public function fakeSendRequestWithError(int $error): void
    {
        Http::fake([
            'https://verify.twilio.com/v2/Services/*/Verifications' => fn () => throw new TwilioException(
                'Request failed',
                $error
            ),
        ]);
    }

    public function fakeVerifyRequest(string $number, bool $valid = true): void
    {
        $number = new PhoneNumber($number);

        $body = [
            'status' => $valid ? 'approved' : 'rejected',
            'payee' => null,
            'date_updated' => '2021-12-02T13:38:09Z',
            'account_sid' => 'aniofandioancdioscnaopdjnaocaejopiof',
            'to' => $number->formatE164(),
            'amount' => null,
            'valid' => $valid,
            'sid' => 'oapmfopadmaifbnaopxnaionadopnaiocnwdaop',
            'date_created' => '2021-12-02T13:37:40Z',
            'service_sid' => 'afoiandopanaopcnawopanmcopamopacoapdn',
            'channel' => 'sms',
        ];

        Http::fake(['https://verify.twilio.com/v2/Services/*/VerificationCheck' => $body]);
    }

    public function fakeVerifyRequestWithExpiredCode(): void
    {
        $body = [
            'code' => 20404,
            'message' => 'The requested resource /Services/VAfff4332abfa276a5b2d806abeb55c6a4/VerificationCheck was not found',
            'more_info' => 'https://www.twilio.com/docs/errors/20404',
            'status' => 404,
        ];

        Http::fake(['https://verify.twilio.com/v2/Services/*/VerificationCheck' => Http::response($body, 404)]);
    }
}
