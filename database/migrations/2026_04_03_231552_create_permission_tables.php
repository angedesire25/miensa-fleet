<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables Spatie Laravel Permission.
     * Reproduit le schéma officiel du package avec nos ajustements.
     *
     * Rôles définis :
     *   super-admin       → Niveau 1 — Accès absolu
     *   admin             → Niveau 2 — Gestion complète
     *   fleet-manager     → Niveau 3 — Opérations quotidiennes
     *   controller        → Niveau 4 — Terrain (fiches, km, infractions)
     *   director          → Niveau 5 — Lecture + rapports
     *   collaborator      → Niveau 6 — Demandes de véhicule
     *   driver-user       → Niveau 7 — Portail chauffeur personnel
     */
    public function up(): void
    {
        $tableNames = [
            'permissions'           => 'permissions',
            'roles'                 => 'roles',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles'       => 'model_has_roles',
            'role_has_permissions'  => 'role_has_permissions',
        ];

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->string('group')->nullable(); // Pour regrouper dans l'UI admin
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->unsignedTinyInteger('level')->default(7); // 1=super-admin … 7=driver
            $table->string('description')->nullable();
            $table->string('color', 10)->nullable(); // Couleur UI ex: #1A3A6B
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
            $table->index('level');
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->foreign('permission_id')
                  ->references('id')
                  ->on($tableNames['permissions'])
                  ->cascadeOnDelete();

            $table->primary(['permission_id', 'model_id', 'model_type'],
                            'model_has_permissions_permission_model_type_primary');
        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->foreign('role_id')
                  ->references('id')
                  ->on($tableNames['roles'])
                  ->cascadeOnDelete();

            $table->primary(['role_id', 'model_id', 'model_type'],
                            'model_has_roles_role_model_type_primary');
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('permission_id')
                  ->references('id')
                  ->on($tableNames['permissions'])
                  ->cascadeOnDelete();
            $table->foreign('role_id')
                  ->references('id')
                  ->on($tableNames['roles'])
                  ->cascadeOnDelete();

            $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
        });

        // Cache Spatie (évite les requêtes BDD à chaque vérification)
        try {
            app('cache')->store(config('permission.cache.store', 'default'))
                        ->forget(config('permission.cache.key', 'spatie.permission.cache'));
        } catch (\Exception $e) {
            // Cache store peut ne pas être disponible lors des migrations initiales
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};