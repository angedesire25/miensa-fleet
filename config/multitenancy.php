<?php

use App\Models\Tenant;
use App\Multitenancy\SubdomainTenantFinder;
use App\Multitenancy\SwitchTenantDatabaseTask;
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Queue\CallQueuedClosure;
use Spatie\Multitenancy\Actions\ForgetCurrentTenantAction;
use Spatie\Multitenancy\Actions\MakeQueueTenantAwareAction;
use Spatie\Multitenancy\Actions\MakeTenantCurrentAction;
use Spatie\Multitenancy\Actions\MigrateTenantAction;
use Spatie\Multitenancy\Jobs\NotTenantAware;
use Spatie\Multitenancy\Jobs\TenantAware;

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant Model
    |--------------------------------------------------------------------------
    */
    'tenant_model' => Tenant::class,

    /*
    |--------------------------------------------------------------------------
    | Tenant Finder
    | Détecte le tenant depuis le sous-domaine de la requête.
    |--------------------------------------------------------------------------
    */
    'tenant_finder' => SubdomainTenantFinder::class,

    /*
    |--------------------------------------------------------------------------
    | Domaine racine (landlord)
    | Production : miensafleet.ci
    | Développement local : miensafleet.test
    |--------------------------------------------------------------------------
    */
    'landlord_domain' => env('LANDLORD_DOMAIN', 'miensafleet.ci'),

    /*
    |--------------------------------------------------------------------------
    | Tenant par défaut en développement local (Laragon)
    | Slug utilisé quand aucun sous-domaine ne correspond à un tenant.
    | Permet d'accéder au panel via l'URL auto-générée (ex: miensa-fleet.test).
    | Laisser null (ou ne pas définir DEV_TENANT_SLUG) en production.
    |--------------------------------------------------------------------------
    */
    'dev_tenant_slug' => env('DEV_TENANT_SLUG', null),

    /*
    |--------------------------------------------------------------------------
    | Switch Tasks
    | Tâches exécutées lors du basculement vers un tenant.
    |--------------------------------------------------------------------------
    */
    'switch_tenant_tasks' => [
        SwitchTenantDatabaseTask::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Connexions DB
    |--------------------------------------------------------------------------
    */
    'tenant_database_connection_name'   => 'tenant',
    'landlord_database_connection_name' => 'landlord',

    /*
    |--------------------------------------------------------------------------
    | Champs de recherche pour la commande tenant:artisan
    |--------------------------------------------------------------------------
    */
    'tenant_artisan_search_fields' => ['id', 'slug'],

    /*
    |--------------------------------------------------------------------------
    | Queues tenant-aware
    |--------------------------------------------------------------------------
    */
    'queues_are_tenant_aware_by_default' => true,

    /*
    |--------------------------------------------------------------------------
    | Context Key
    |--------------------------------------------------------------------------
    */
    'current_tenant_context_key'   => 'tenantId',
    'current_tenant_container_key' => 'currentTenant',

    'shared_routes_cache' => false,

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */
    'actions' => [
        'make_tenant_current_action'      => MakeTenantCurrentAction::class,
        'forget_current_tenant_action'    => ForgetCurrentTenantAction::class,
        'make_queue_tenant_aware_action'  => MakeQueueTenantAwareAction::class,
        'migrate_tenant'                  => MigrateTenantAction::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Queueable → Job resolution
    |--------------------------------------------------------------------------
    */
    'queueable_to_job' => [
        SendQueuedMailable::class       => 'mailable',
        SendQueuedNotifications::class  => 'notification',
        CallQueuedClosure::class        => 'closure',
        CallQueuedListener::class       => 'class',
        BroadcastEvent::class           => 'event',
    ],

    'tenant_aware_interface'     => TenantAware::class,
    'not_tenant_aware_interface' => NotTenantAware::class,

    'tenant_aware_jobs'     => [],
    'not_tenant_aware_jobs' => [],
];
