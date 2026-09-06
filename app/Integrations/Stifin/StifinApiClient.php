<?php

namespace App\Integrations\Stifin;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

class StifinApiClient
{
    private string $baseUrl;

    private string $userIdIdentifier;

    private int $connectTimeoutSec;

    private int $totalTimeoutSec;

    private ?string $authHeader;

    private ?string $authValue;

    public function __construct(
        ?string $baseUrl = null,
        ?string $userIdIdentifier = null,
        ?int $connectTimeoutSec = null,
        ?int $totalTimeoutSec = null,
        ?string $authHeader = null,
        ?string $authValue = null,
    ) {
        $dbResolved = null;
        if ($baseUrl === null && $userIdIdentifier === null && $connectTimeoutSec === null && $totalTimeoutSec === null && $authHeader === null && $authValue === null) {
            try {
                $resolver = new StifinApiCredentialResolver;
                $resolved = $resolver->resolvePrimaryCredentials();
                if (($resolved['source'] ?? '') === 'db_integration_connection') {
                    $dbResolved = $resolved;
                }
            } catch (Throwable) {
                $dbResolved = null;
            }
        }

        $defaultBase = $dbResolved['base_url'] ?? config('services.stifin.base_url', 'https://apro.stifin.id/api');
        $defaultUser = $dbResolved['user_id_identifier'] ?? config('services.stifin.user_id', 'STIFLOW-SYSTEM');
        $defaultAuthHeader = $dbResolved['auth_header'] ?? config('services.stifin.auth_header');
        $defaultAuthValue = $dbResolved['auth_value'] ?? config('services.stifin.auth_value');
        $defaultConnect = $dbResolved['connect_timeout'] ?? (int) config('services.stifin.connect_timeout', 15);
        $defaultTotal = $dbResolved['total_timeout'] ?? (int) config('services.stifin.timeout', 30);

        $this->baseUrl = rtrim($baseUrl ?? $defaultBase, '/');
        $this->userIdIdentifier = $userIdIdentifier ?? $defaultUser;
        $this->connectTimeoutSec = $connectTimeoutSec ?? (int) $defaultConnect;
        $this->totalTimeoutSec = $totalTimeoutSec ?? (int) $defaultTotal;
        $this->authHeader = $this->normalizeOptionalCredential($authHeader ?? $defaultAuthHeader);
        $this->authValue = $this->normalizeOptionalCredential($authValue ?? $defaultAuthValue);
    }

    public function getUserIdIdentifier(): string
    {
        return $this->userIdIdentifier;
    }

    /**
     * GET /proGetCab/pro/{branchCode}
     *
     * @return list<array>
     */
    public function listPromotersOfBranch(string $branchCode): array
    {
        $result = $this->listPromotersOfBranchResult($branchCode);
        if ($result->isSuccess && is_array($result->parsedData)) {
            return $result->parsedData;
        }

        return [];
    }

    public function listPromotersOfBranchResult(string $branchCode): StifinOperationResult
    {
        $url = "{$this->baseUrl}/proGetCab/pro/".urlencode($branchCode);

        return $this->doRequest('GET', $url);
    }

    /**
     * GET /voucherGet/getTotVoucher/{promoterCode}
     *
     * @return array{paid:int,free:int}
     */
    public function getVoucherBalance(string $promoterCode): array
    {
        $url = "{$this->baseUrl}/voucherGet/getTotVoucher/".urlencode($promoterCode);
        $result = $this->doRequest('GET', $url);

        if (! $result->isSuccess || ! is_array($result->parsedData)) {
            throw new \RuntimeException('Saldo voucher STIFIN tidak dapat dibaca.');
        }

        $data = $result->parsedData;
        if (isset($data['data']) && is_array($data['data'])) {
            $data = $data['data'];
        }
        if (array_is_list($data) && isset($data[0]) && is_array($data[0])) {
            $data = $data[0];
        }

        $paidValue = $data['SaldoJ'] ?? $data['paid'] ?? $data['total_paid'] ?? null;
        $freeValue = $data['SaldoF'] ?? $data['free'] ?? $data['total_free'] ?? null;

        if (! is_numeric($paidValue) || ! is_numeric($freeValue)) {
            throw new \RuntimeException('Format saldo voucher STIFIN tidak valid.');
        }

        return ['paid' => (int) $paidValue, 'free' => (int) $freeValue];
    }

    /**
     * POST /voucherPos/editCabVoucher/{branchCode}
     *
     * Payload fields (sesuai audit plugin referensi):
     *   KodeID, Jumlah, JmlFree, SaldoJ, SaldoF, Dispos, Ket, UserID
     */
    public function addVoucher(string $branchCode, array $payload): StifinOperationResult
    {
        $url = "{$this->baseUrl}/voucherPos/editCabVoucher/".urlencode($branchCode);

        return $this->doRequest('POST', $url, $payload, true);
    }

    /**
     * Request helper dengan klasifikasi hasil yang cermat.
     */
    private function doRequest(
        string $method,
        string $url,
        array $payload = [],
        bool $isWriteOperation = false,
    ): StifinOperationResult {
        $startedAt = microtime(true);

        try {
            $http = Http::acceptJson()
                ->timeout($this->totalTimeoutSec)
                ->connectTimeout($this->connectTimeoutSec)
                ->withOptions(['verify' => true]);

            if ($this->authHeader !== null && $this->authValue !== null) {
                $http = $http->withHeader($this->authHeader, $this->authValue);
            }

            $response = $method === 'GET'
                ? $http->get($url)
                : $http->asJson()->post($url, $payload);

            $httpCode = $response->status();
            $rawBody = $response->body() ?? '';

            $parsed = null;
            $parseError = false;
            if (trim($rawBody) === '' || trim($rawBody) === '0') {
                $parseError = true;
            } else {
                $json = json_decode($rawBody, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                    $parsed = $json;
                } else {
                    $parseError = true;
                }
            }

            $isSuccess = $response->successful() && ! $parseError;

            return new StifinOperationResult(
                isSuccess: $isSuccess,
                httpCode: $httpCode,
                rawBody: $rawBody,
                parsedData: $parsed,
                isParseError: $parseError,
            );
        } catch (ConnectionException $e) {
            $elapsed = microtime(true) - $startedAt;
            $isTimeout = stripos($e->getMessage(), 'timeout') !== false
                || stripos($e->getMessage(), 'timed out') !== false
                || $elapsed >= ($this->connectTimeoutSec - 1);

            if ($isWriteOperation) {
                if ($isTimeout) {
                    return new StifinOperationResult(
                        isSuccess: false,
                        httpCode: null,
                        rawBody: '',
                        parsedData: null,
                        isAmbiguousTimeout: true,
                        errorMessage: $e->getMessage(),
                    );
                }

                return new StifinOperationResult(
                    isSuccess: false,
                    httpCode: null,
                    rawBody: '',
                    parsedData: null,
                    isAmbiguousTransport: true,
                    errorMessage: $e->getMessage(),
                );
            }

            return new StifinOperationResult(
                isSuccess: false,
                httpCode: null,
                rawBody: '',
                parsedData: null,
                errorMessage: $e->getMessage(),
            );
        } catch (Throwable $e) {
            return new StifinOperationResult(
                isSuccess: false,
                httpCode: null,
                rawBody: '',
                parsedData: null,
                isParseError: false,
                errorMessage: $e->getMessage(),
            );
        }
    }

    private function normalizeOptionalCredential(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
