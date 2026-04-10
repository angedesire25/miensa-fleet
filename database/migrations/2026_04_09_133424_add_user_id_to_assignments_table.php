<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // Rendre driver_id nullable (un collaborateur n'a pas de fiche driver)
            $table->foreignId('driver_id')->nullable()->change();
            // Ajouter user_id pour les collaborateurs
            $table->foreignId('user_id')->nullable()->after('driver_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->foreignId('driver_id')->nullable(false)->change();
        });
    }
};
