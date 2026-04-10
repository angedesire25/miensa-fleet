<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE assignments MODIFY COLUMN type ENUM(
            'mission',
            'daily',
            'permanent',
            'replacement',
            'trial',
            'courses'
        ) NOT NULL DEFAULT 'mission'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE assignments MODIFY COLUMN type ENUM(
            'mission',
            'daily',
            'permanent',
            'replacement',
            'trial'
        ) NOT NULL DEFAULT 'mission'");
    }
};
