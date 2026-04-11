<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historique des abonnements par tenant.
 */
return new class extends Migration
{
    public function getConnection(): string { return 'landlord'; }

    public function up(): void
    {
        Schema::connection('landlord')->create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans');

            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->decimal('amount', 10, 2);               // Montant facturé
            $table->string('currency', 3)->default('XOF');  // FCFA

            $table->enum('status', ['active', 'past_due', 'cancelled', 'trial'])
                  ->default('trial');

            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Référence paiement externe (Wave, Orange Money, CinetPay...)
            $table->string('payment_reference')->nullable();
            $table->string('payment_provider')->nullable();   // "wave", "orange_money", "cinetpay"

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('subscriptions');
    }
};
