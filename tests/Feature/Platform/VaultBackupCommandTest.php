<?php

namespace Tests\Feature\Platform;

use App\Tools\Quiz\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VaultBackupCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_writes_a_fresh_pool_export_and_runs_the_backup(): void
    {
        Storage::fake('local');
        Question::factory()->fillBlank('a')->create(['prompt' => 'What is dependency injection?']);

        $this->artisan('vault:backup')->assertExitCode(0);

        Storage::disk('local')->assertExists('backup-exports/pool.json');
        $payload = json_decode(Storage::disk('local')->get('backup-exports/pool.json'), true);
        $this->assertSame('What is dependency injection?', $payload['questions'][0]['prompt']);
    }
}
