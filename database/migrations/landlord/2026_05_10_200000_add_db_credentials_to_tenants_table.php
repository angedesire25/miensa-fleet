<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function getConnection(): string { return 'landlord'; }

    public function up(): void
    {
        Schema::connection('landlord')->table('tenants', function (Blueprint $table) {
            // Credentials de connexion propres à chaque tenant
            // Nullable → les tenants existants héritent des valeurs par défaut de l'env
            $table->string('db_host')->nullable()->after('database');
            $table->unsignedSmallInteger('db_port')->default(3306)->after('db_host');
            $table->string('db_username')->nullable()->after('db_port');
            $table->text('db_password')->nullable()->after('db_username'); // chiffré avec Crypt
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->table('tenants', function (Blueprint $table) {
            $table->dropColumn(['db_host', 'db_port', 'db_username', 'db_password']);
        });
    }
};
