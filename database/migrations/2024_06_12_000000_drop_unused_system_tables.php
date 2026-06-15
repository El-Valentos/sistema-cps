<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['failed_jobs', 'sessions', 'password_reset_tokens', 'cache', 'cache_locks'];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        DB::table('migrations')
            ->whereIn('migration', [
                '0001_01_01_000002_create_password_resets_table',
                '0001_01_01_000003_create_failed_jobs_table',
                '0001_01_01_000004_create_sessions_table',
                '0001_01_01_000006_create_cache_table',
            ])
            ->delete();
    }

    public function down(): void
    {
    }
};
