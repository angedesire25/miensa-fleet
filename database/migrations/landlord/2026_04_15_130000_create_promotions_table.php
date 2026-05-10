<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'landlord';

    public function up(): void
    {
        Schema::connection('landlord')->create('promotions', function (Blueprint $table) {
            $table->id();

            $table->string('label');                          // "Promo lancement"
            $table->string('badge_text')->nullable();         // "-20%" affiché sur la carte
            $table->text('description')->nullable();          // Message sous le badge

            // Remise
            $table->enum('discount_type', ['percent', 'fixed'])->default('percent');
            $table->decimal('discount_value', 10, 2);         // 20 = 20% ou 10000 FCFA

            // Applicabilité
            $table->unsignedBigInteger('plan_id')->nullable(); // null = tous les plans
            $table->enum('billing_period', ['all', 'monthly', 'yearly'])->default('all');

            // Validité
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('plan_id')
                  ->references('id')->on('plans')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('promotions');
    }
};
