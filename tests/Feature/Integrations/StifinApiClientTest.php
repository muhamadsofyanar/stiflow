<?php

namespace Tests\Feature\Integrations;

use App\Enums\StifinOperationOutcome;
use App\Integrations\Stifin\StifinApiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class StifinApiClientTest extends TestCase
{
    public function test_add_voucher_sends_json_and_configured_auth_header(): void
    {
        Http::fake([
            'https://stifin.test/*' => Http::response(['success' => true], 200),
        ]);

        $client = new StifinApiClient(
            baseUrl: 'https://stifin.test/api',
            userIdIdentifier: 'STIFLOW-KHU',
            connectTimeoutSec: 5,
            totalTimeoutSec: 10,
            authHeader: 'Authorization',
            authValue: 'Bearer secret-value',
        );

        $result = $client->addVoucher('KHU', [
            'KodeID' => 'KHU-ABU-02',
            'Jumlah' => '1',
            'JmlFree' => '0',
            'SaldoJ' => '10',
            'SaldoF' => '0',
            'Dispos' => 'ORDER-1',
            'Ket' => 'Order 1',
            'UserID' => 'STIFLOW-KHU',
        ]);

        $this->assertTrue($result->isSuccess);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://stifin.test/api/voucherPos/editCabVoucher/KHU'
                && $request->hasHeader('Accept', 'application/json')
                && $request->hasHeader('Authorization', 'Bearer secret-value')
                && str_starts_with($request->header('Content-Type')[0] ?? '', 'application/json')
                && $request->data()['KodeID'] === 'KHU-ABU-02'
                && $request->data()['Jumlah'] === '1';
        });
    }

    public function test_successful_unparseable_response_is_classified_unparseable(): void
    {
        Http::fake([
            'https://stifin.test/*' => Http::response('<html>not-json</html>', 200),
        ]);

        $client = new StifinApiClient(
            baseUrl: 'https://stifin.test/api',
            userIdIdentifier: 'STIFLOW-KHU',
            connectTimeoutSec: 5,
            totalTimeoutSec: 10,
            authHeader: null,
            authValue: null,
        );

        $result = $client->addVoucher('KHU', [
            'KodeID' => 'KHU-ABU-02',
        ]);

        $this->assertFalse($result->isSuccess);
        $this->assertTrue($result->isParseError);
        $this->assertSame(StifinOperationOutcome::Unparseable, $result->outcome());
    }

    public function test_empty_two_xx_write_response_is_not_treated_as_success(): void
    {
        Http::fake([
            'https://stifin.test/*' => Http::response('', 200),
        ]);

        $client = new StifinApiClient(
            baseUrl: 'https://stifin.test/api',
            userIdIdentifier: 'STIFLOW-KHU',
            connectTimeoutSec: 5,
            totalTimeoutSec: 10,
        );

        $result = $client->addVoucher('KHU', ['KodeID' => 'KHU-ABU-02']);

        $this->assertFalse($result->isSuccess);
        $this->assertSame(StifinOperationOutcome::Unparseable, $result->outcome());
    }

    public function test_failed_balance_read_never_falls_back_to_zero_balance(): void
    {
        Http::fake([
            'https://stifin.test/*' => Http::response(['message' => 'maintenance'], 503),
        ]);

        $client = new StifinApiClient(
            baseUrl: 'https://stifin.test/api',
            userIdIdentifier: 'STIFLOW-KHU',
            connectTimeoutSec: 5,
            totalTimeoutSec: 10,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Saldo voucher STIFIN tidak dapat dibaca.');

        $client->getVoucherBalance('KHU-ABU-02');
    }
}
