<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductionRuntimeFilesTest extends TestCase
{
    #[DataProvider('runtimeFiles')]
    public function test_required_runtime_file_exists_and_is_not_empty(string $relativePath): void
    {
        $path = base_path($relativePath);

        $this->assertFileExists($path);
        $this->assertGreaterThan(0, filesize($path));
    }

    public static function runtimeFiles(): array
    {
        return array_map(fn (string $path): array => [$path], [
            'Dockerfile',
            '.dockerignore',
            'compose.production.yaml',
            'docker/entrypoint.sh',
            'docker/nginx.conf',
            'docker/supervisord.conf',
            'docker/runtime-smoke.sh',
            'docs/DEPLOYMENT.md',
            'docs/COOLIFY-UPDATE.md',
            'docs/BACKUP-RESTORE.md',
        ]);
    }

    public function test_production_compose_disables_prototype_modules(): void
    {
        $compose = file_get_contents(base_path('compose.production.yaml'));

        $this->assertIsString($compose);
        $this->assertStringContainsString('STIFLOW_PROTOTYPE_MODULES: "false"', $compose);
    }
}
