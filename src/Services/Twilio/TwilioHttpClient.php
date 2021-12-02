<?php

declare(strict_types=1);

namespace Worksome\VerifyByPhone\Services\Twilio;

use GuzzleHttp\Psr7\Query;
use Illuminate\Support\Facades\Http;
use Twilio\Http\Client;
use Twilio\Http\Response;

/**
 * A Twilio HTTP client implementation that uses Laravel's Http Client
 * to allow for easy faking of responses in tests.
 *
 * @internal
 */
final class TwilioHttpClient implements Client
{
    public function request(
        string $method,
        string $url,
        array $params = [],
        array $data = [],
        array $headers = [],
        string $user = null,
        string $password = null,
        int $timeout = null,
    ): Response {
        $body = Query::build($data, PHP_QUERY_RFC1738);

        $request = Http::withBody($body, 'application/x-www-form-urlencoded');

        if ($user && $password) {
            $request = $request->withBasicAuth($user, $password);
        }

        if ($params) {
            $request->mergeOptions(['query' => $params]);
        }

        if ($timeout) {
            $request = $request->timeout($timeout);
        }

        $response = $request->send($method, $url);

        return new Response($response->status(), $response->body(), $response->headers());
    }
}
