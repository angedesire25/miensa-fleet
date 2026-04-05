<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute la prise en charge du conducteur sur une demande de véhicule :
     *   - driver_id   : chauffeur professionnel affecté par le gestionnaire (nullable)
     *   - self_driving : le demandeur conduit lui-même (permis vérifié par le gestionnaire)
     */
    public function up(): void
    {
        Schema::table('vehicle_requests', function (Blueprint $table) {
            $table->foreignId('driver_id')
                  ->nullable()
                  ->after('vehicle_id')
                  ->constrained('drivers')
                  ->nullOnDelete();

            $table->boolean('self_driving')
                  ->default(false)
                  ->after('driver_id')
                  ->comment('true = le demandeur conduit lui-même');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_requests', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Driver::class);
            $table->dropColumn(['driver_id', 'self_driving']);
        });
    }
};
