<?php

declare(strict_types=1);

namespace Foundwell\Http;

use Foundwell\Contracts\TransportInterface;
use Foundwell\Exceptions\ConnectionException;
use Foundwell\Exceptions\TimeoutException;

final class CurlTransport implements TransportInterface
{
    public function send(Request $request): Response
    {
        if (!function_exists('curl_init')) {
            throw new ConnectionException('The PHP cURL extension is required.');
        }

        $responseHeaders = [];
        $curl = curl_init($request->url());
        if ($curl === false) {
            throw new ConnectionException('Unable to initialize cURL.');
        }

        $headerLines = [];
        foreach ($request->headers() as $name => $value) {
            $headerLines[] = $name . ': ' . $value;
        }

        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $request->method(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => $request->connectTimeout(),
            CURLOPT_TIMEOUT => $request->requestTimeout(),
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_HEADERFUNCTION => static function ($handle, string $line) use (&$responseHeaders): int {
                $length = strlen($line);
                $line = trim($line);
                if ($line === '' || strpos($line, ':') === false) {
                    return $length;
                }
                [$name, $value] = explode(':', $line, 2);
                $responseHeaders[trim($name)] = trim($value);
                return $length;
            },
        ]);

        if ($request->body() !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request->body());
        }

        $body = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if ($body === false) {
            if ($errno === CURLE_OPERATION_TIMEDOUT) {
                throw new TimeoutException('Foundwell Platform request timed out: ' . $error);
            }
            throw new ConnectionException('Unable to reach Foundwell Platform: ' . $error);
        }

        return new Response($status, $responseHeaders, (string) $body);
    }
}
