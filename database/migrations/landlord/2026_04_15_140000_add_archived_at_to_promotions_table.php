<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'landlord';

    public function up(): void
    {
        Schema::connection('landlord')->table('promotions', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->table('promotions', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });
    }
};
