<?php

use Illuminate\Database\Migrations\Migration;

/**
 * La table `repairs` est déjà créée avec les valeurs correctes :
 *   corrective | preventive | warranty | recall
 *
 * Cette migration est conservée pour ne pas casser l'historique.
 * Elle ne fait rien : toute modification de l'enum était erronée.
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op : l'enum corrective/preventive/warranty/recall est correct dès la création.
    }

    public function down(): void
    {
        // No-op
    }
};
