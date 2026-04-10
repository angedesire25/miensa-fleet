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
        Schema::create('part_replacements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('repair_id')->nullable()->constrained('repairs')->nullOnDelete();
            $table->foreignId('incident_id')->nullable()->constrained('incidents')->nullOnDelete();

            // Identification de la pièce
            $table->string('part_category')->nullable();
            $table->string('part_name');
            $table->string('part_reference')->nullable();
            $table->string('part_brand')->nullable();

            // Coûts
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();

            // Pose
            $table->date('replaced_at')->nullable();
            $table->unsignedInteger('km_at_replacement')->nullable();
            $table->foreignId('replaced_by_garage')->nullable()->constrained('garages')->nullOnDelete();

            // Garantie
            $table->unsignedSmallInteger('warranty_months')->nullable();
            $table->date('warranty_expiry')->nullable();

            // Suivi défaillance
            $table->date('failed_at')->nullable();
            $table->foreignId('failure_reported_in_repair_id')->nullable()->constrained('repairs')->nullOnDelete();
            $table->unsignedInteger('days_until_failure')->nullable();
            $table->string('failure_reason')->nullable();
            $table->boolean('under_warranty_at_failure')->nullable();

            // Statut
            $table->enum('status', ['active', 'failed', 'removed'])->default('active');
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('part_replacements');
    }
};
