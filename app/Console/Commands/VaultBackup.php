<?php

namespace App\Console\Commands;

use App\Services\PoolExportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Refreshes the pool-export snapshot that `config/backup.php` bundles into
 * the zip, then runs the standard spatie/laravel-backup dump (which backs
 * up the sqlite database itself via the 'databases' source).
 */
class VaultBackup extends Command
{
    protected $signature = 'vault:backup';

    protected $description = 'Write a fresh question-pool export and run the scheduled vault backup';

    public function handle(PoolExportService $exporter): int
    {
        Storage::disk('local')->put('backup-exports/pool.json', $exporter->toJson());

        return $this->call('backup:run');
    }
}
