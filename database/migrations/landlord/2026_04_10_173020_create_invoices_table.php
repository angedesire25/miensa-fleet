<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Factures émises par MiensaFleet aux clients (landlord only).
 */
return new class extends Migration
{
    public function getConnection(): string { return 'landlord'; }

    public function up(): void
    {
        Schema::connection('landlord')->create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();

            $table->string('invoice_number')->unique();      // "INV-2026-00001"
            $table->decimal('amount_ht', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(18); // TVA Côte d'Ivoire
            $table->decimal('amount_ttc', 10, 2);
            $table->string('currency', 3)->default('XOF');

            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])
                  ->default('draft');

            $table->date('issued_at');
            $table->date('due_at');
            $table->date('paid_at')->nullable();

            $table->string('payment_reference')->nullable();
            $table->string('payment_provider')->nullable();

            $table->text('notes')->nullable();
            $table->string('pdf_path')->nullable();         // chemin du PDF généré

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('invoices');
    }
};
