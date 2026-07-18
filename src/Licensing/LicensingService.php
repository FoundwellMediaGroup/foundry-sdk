<?php

declare(strict_types=1);

namespace Foundwell\Licensing;

use Foundwell\Config;
use Foundwell\Contracts\LicensingInterface;
use Foundwell\Exceptions\ActivationLimitException;
use Foundwell\Exceptions\ApiException;
use Foundwell\Exceptions\AuthorizationException;
use Foundwell\Exceptions\ConnectionException;
use Foundwell\Exceptions\LicenseRejectedException;
use Foundwell\Exceptions\TimeoutException;
use Foundwell\Exceptions\ValidationException;
use Foundwell\Http\HttpClient;

final class LicensingService implements LicensingInterface
{
    private Config $config;
    private HttpClient $http;
    private ?InstallationIdentity $identity;
    private LicenseCache $cache;

    public function __construct(Config $config, HttpClient $http, ?InstallationIdentity $identity = null)
    {
        $this->config = $config;
        $this->http = $http;
        $this->identity = $identity;
        $this->cache = new LicenseCache($config->licenseCachePath());
    }

    public function activate(string $licenseKey, ?string $fingerprint = null, ?string $hostname = null): LicenseResult
    {
        $identity = $this->resolveIdentity($fingerprint, $hostname);
        $payload = $this->request('/api/v1/licenses/activate', [
            'license_key' => $this->licenseKey($licenseKey),
            'product' => $this->config->product(),
            'fingerprint' => $identity->fingerprint(),
            'hostname' => $identity->hostname(),
            'version' => $this->config->version(),
        ]);
        $result = LicenseResult::fromPayload($payload);
        if ($result->isValid() && $result->status() === 'active') {
            $this->cache->store($payload, $licenseKey, $identity->fingerprint());
        }
        return $result;
    }

    public function validate(string $licenseKey, ?string $fingerprint = null): LicenseResult
    {
        $identity = $this->resolveIdentity($fingerprint, null);
        try {
            $payload = $this->request('/api/v1/licenses/validate', [
                'license_key' => $this->licenseKey($licenseKey),
                'product' => $this->config->product(),
                'fingerprint' => $identity->fingerprint(),
                'version' => $this->config->version(),
            ]);
        } catch (LicenseRejectedException $exception) {
            $payload = $exception->payload();
            if ($payload !== []) {
                return LicenseResult::fromPayload($payload);
            }
            throw $exception;
        } catch (ConnectionException | TimeoutException $exception) {
            $cached = $this->cache->load($licenseKey, $identity->fingerprint());
            if ($cached !== null) {
                return $cached;
            }
            throw $exception;
        }
        $result = LicenseResult::fromPayload($payload);
        if ($result->isValid() && $result->status() === 'active') {
            $this->cache->store($payload, $licenseKey, $identity->fingerprint());
        }
        return $result;
    }

    public function deactivate(string $licenseKey, ?string $fingerprint = null): LicenseResult
    {
        $identity = $this->resolveIdentity($fingerprint, null);
        $payload = $this->request('/api/v1/licenses/deactivate', [
            'license_key' => $this->licenseKey($licenseKey),
            'product' => $this->config->product(),
            'fingerprint' => $identity->fingerprint(),
        ]);
        return LicenseResult::fromPayload($payload);
    }

    /** @param array<string,mixed> $payload
     *  @return array<string,mixed>
     */
    private function request(string $path, array $payload): array
    {
        try {
            return $this->http->request('POST', $path, $payload)->json();
        } catch (AuthorizationException $exception) {
            throw new LicenseRejectedException($exception->getMessage(), $exception->getCode(), $exception->requestId(), $exception, $exception->payload());
        } catch (ApiException $exception) {
            if ($exception->getCode() === 409) {
                throw new ActivationLimitException($exception->getMessage(), 409, $exception->requestId(), $exception, $exception->payload());
            }
            throw $exception;
        }
    }

    private function resolveIdentity(?string $fingerprint, ?string $hostname): InstallationIdentity
    {
        if ($fingerprint !== null) {
            return new InstallationIdentity($fingerprint, $hostname ?? ($this->identity ? $this->identity->hostname() : null));
        }
        if ($this->identity === null) {
            throw new ValidationException('No installation identity was configured and no fingerprint was supplied.');
        }
        return $this->identity;
    }

    private function licenseKey(string $licenseKey): string
    {
        $licenseKey = trim($licenseKey);
        if ($licenseKey === '') {
            throw new ValidationException('A license key is required.');
        }
        return $licenseKey;
    }
}
