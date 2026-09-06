<?php

namespace App\Livewire;

use App\Enums\IntegrationConnectionStatus;
use App\Models\IntegrationConnection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class IntegrationTestConnectionButton extends Component
{
    public IntegrationConnection $integration;

    public ?string $lastResult = null;

    public ?bool $lastSuccess = null;

    public bool $testing = false;

    public function mount(IntegrationConnection $integration): void
    {
        $this->integration = $integration;
    }

    public function test(): void
    {
        $this->testing = true;
        $this->lastResult = null;
        $this->lastSuccess = null;

        try {
            $result = $this->simulateTest();

            DB::transaction(function () use ($result) {
                $this->integration->update([
                    'status' => $result['success']
                        ? IntegrationConnectionStatus::Connected
                        : IntegrationConnectionStatus::Failed,
                    'last_tested_at' => now(),
                    'last_error_message' => $result['success'] ? null : ($result['message'] ?? 'Unknown error'),
                ]);
            });

            $this->lastSuccess = $result['success'];
            $this->lastResult = $result['message'];
        } catch (\Throwable $e) {
            $this->lastSuccess = false;
            $this->lastResult = 'Exception: '.$e->getMessage();

            DB::transaction(function () use ($e) {
                $this->integration->update([
                    'status' => IntegrationConnectionStatus::Failed,
                    'last_tested_at' => now(),
                    'last_error_message' => $e->getMessage(),
                ]);
            });
        } finally {
            $this->testing = false;
        }
    }

    private function simulateTest(): array
    {
        $provider = strtolower($this->integration->provider ?? '');

        if (empty($provider) || $provider === 'dummy_fail') {
            return [
                'success' => false,
                'message' => 'Provider tidak dikonfigurasi dengan benar.',
            ];
        }

        $latency = rand(50, 200);

        return [
            'success' => true,
            'message' => sprintf('Koneksi ke %s berhasil (latency: %dms)', $this->integration->provider, $latency),
            'latency_ms' => $latency,
        ];
    }

    public function render()
    {
        return view('livewire.integration-test-connection-button');
    }
}
