<?php

namespace SuperV\Platform\Domains\Resource\Support;

use Current;
use Illuminate\Database\Schema\Blueprint as LaravelBlueprint;
use Schema as LaravelSchema;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\ResourceConfig as Config;
use SuperV\Platform\Domains\Resource\ResourceModel;

class PlatformBlueprints
{
    public static $resources = [
        'namespaces' => 'sv_namespaces',
        'resources'  => 'sv_resources',
        'fields'     => 'sv_fields',
        'forms'      => 'sv_forms',
        'relations'  => 'sv_relations',
        'navigation' => 'sv_navigation',
        'activities' => 'sv_activities',
    ];

    public static function createTables()
    {
        foreach (PlatformBlueprints::$resources as $resource => $table) {
            LaravelSchema::create($table,
                function (LaravelBlueprint $table) use ($resource) {
                    PlatformBlueprints::{$resource}($table);
                }
            );
        }
    }

    public static function createResources()
    {
        Current::setMigrationScope('sv.platform');

        foreach (PlatformBlueprints::$resources as $resource => $table) {
            Schema::run($table,
                function (Blueprint $table, Config $config) use ($resource) {
                    $config->handle($resource);
                    PlatformBlueprints::{$resource}($table, $config);
                }
            );
        }
    }

    public static function dropTables()
    {
        foreach (PlatformBlueprints::$resources as $resource => $table) {
            Schema::dropIfExists($table);
        }

        Schema::dropIfExists('sv_form_fields');
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint    $table
     * @param \SuperV\Platform\Domains\Resource\ResourceConfig|null $config
     */
    public static function namespaces($table, ResourceConfig $config = null)
    {
        $table->increments('id');

        $table->string('namespace')->showOnIndex()->unique()->searchable();

        if ($table instanceof Blueprint) {
            $config->label('Namespaces');
            $config->handle('namespaces');
//            $config->nav('acp.platform.system');

            $table->select('type', ['resource', 'form', 'field'])->showOnIndex()->addFlag('filter');

            $table->createdBy()->updatedBy();
        } else {
            $table->string('type');

            $table->nullableTimestamps();
            $table->unsignedInteger('created_by_id')->nullable();
            $table->unsignedInteger('updated_by_id')->nullable();
        }
        $table->boolean('restorable')->default(false);
        $table->boolean('sortable')->default(false);
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint    $table
     * @param \SuperV\Platform\Domains\Resource\ResourceConfig|null $config
     */
    public static function resources($table, ResourceConfig $config = null)
    {
        $table->increments('id');
        $table->uuid('uuid')->unique();
//        $table->uuid('rev_id')->nullable()->unique();

        $table->string('name')->showOnIndex()->entryLabel();
        $table->string('handle')->showOnIndex()->entryLabel();
        $table->string('identifier')->showOnIndex()->unique();
        $table->string('namespace');

        $table->string('model')->nullable();
        $table->string('dsn');

        if ($table instanceof Blueprint) {
            $config->model(ResourceModel::class);
            $config->label('Resources');
            $config->resourceKey('resource');
            $config->nav('acp.platform.system');

            $table->hasMany('sv.platform.fields', 'fields');
            $table->hasMany('sv.platform.relations', 'relations');
            $table->hasMany('sv.platform.forms', 'forms');
            $table->hasMany('sv.platform.activities', 'activities');
            $table->dictionary('config')->nullable();

            $table->createdBy()->updatedBy();
        } else {
            $table->text('config')->nullable();

            $table->nullableTimestamps();
            $table->unsignedInteger('created_by_id')->nullable();
            $table->unsignedInteger('updated_by_id')->nullable();
        }
        $table->boolean('pivot')->default(false);
        $table->boolean('restorable')->default(false);
        $table->boolean('sortable')->default(false);
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint    $table
     * @param \SuperV\Platform\Domains\Resource\ResourceConfig|null $config
     */
    public static function fields($table, ResourceConfig $config = null)
    {
        $table->increments('id');

        $table->string('identifier')->unique()->showOnIndex();

        $table->uuid('revision_id')->unique();

        if ($table instanceof Blueprint) {
            $config->model(FieldModel::class);
            $config->label('Fields');
            $config->resourceKey('field');
            $config->nav('acp.platform.system');

            $table->nullableBelongsTo('sv.platform.resources', 'resource')->showOnIndex();

            $table->text('flags')->fieldType('array')->nullable();
            $table->dictionary('rules')->nullable();
            $table->dictionary('config')->nullable();
        } else {
            $table->unsignedInteger('resource_id')->nullable();

            $table->text('flags')->nullable();
            $table->text('rules')->nullable();
            $table->text('config')->nullable();
        }

        $table->string('label')->showOnIndex();
//        $table->string('name')->showOnIndex();
        $table->string('handle')->showOnIndex();
        $table->string('column_type')->nullable();
        $table->string('type')->showOnIndex();

        $table->timestamps();
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint    $table
     * @param \SuperV\Platform\Domains\Resource\ResourceConfig|null $config
     */
    public static function forms($table, ResourceConfig $config = null)
    {
        $table->increments('id');

        $table->string('uuid')->unique();
//        $table->uuid('rev_id')->nullable()->unique();

        $table->string('name')->showOnIndex();
        $table->string('identifier')->showOnIndex()->unique();
        $table->string('namespace')->showOnIndex()->nullable();

        if ($table instanceof Blueprint) {
            $config->label('Forms');
            $config->resourceKey('form');
            $config->nav('acp.platform.system');
            $config->model(FormModel::class);

            $table->nullableBelongsTo('sv.platform.resources', 'resource');
//            $table->hasUuid()->showOnIndex();

            $table->createdBy()->updatedBy();

            $table->belongsToMany('sv.platform.fields', 'fields')
                  ->pivotTable('sv_form_fields', 'sv.platform.form_fields')
                  ->pivotForeignKey('form_id')
                  ->pivotRelatedKey('field_id')
                  ->pivotColumns(function (Blueprint $pivotTable) {
                      $pivotTable->unsignedInteger('sort_order')->nullable();
                  });
        } else {
            $table->unsignedInteger('resource_id')->nullable();

            $table->timestamps();
            $table->unsignedInteger('created_by_id')->nullable();
            $table->unsignedInteger('updated_by_id')->nullable();
        }

        $table->string('title')->showOnIndex();
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint    $table
     * @param \SuperV\Platform\Domains\Resource\ResourceConfig|null $config
     */
    public static function relations($table, ResourceConfig $config = null)
    {
        $table->increments('id');
        $table->uuid('uuid')->unique();
        if ($table instanceof Blueprint) {
            $config->label('Relations');
            $config->resourceKey('relation');
            $config->nav('acp.platform.system');

            $table->belongsTo('sv.platform.resources', 'resource')->showOnIndex();
            $table->dictionary('config')->nullable();
        } else {
            $table->unsignedInteger('resource_id');
            $table->text('config')->nullable();
        }

        $table->string('name')->showOnIndex();
        $table->string('type')->showOnIndex();

        $table->timestamps();
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint    $table
     * @param \SuperV\Platform\Domains\Resource\ResourceConfig|null $config
     */
    public static function navigation($table, ResourceConfig $config = null)
    {
        $table->increments('id');
        if ($table instanceof Blueprint) {
            $config->label('Navigation');
            $config->resourceKey('nav');
            $config->nav('acp.platform.system');

            $table->nullableBelongsTo('sv.platform.navigation', 'parent')->showOnIndex();
            $table->nullableBelongsTo('sv.platform.resources', 'resource')->showOnIndex();
        } else {
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('resource_id')->nullable();
        }

        $table->string('title')->entryLabel();
        $table->string('handle');
        $table->string('namespace')->nullable();
        $table->string('icon')->nullable();
        $table->string('url')->nullable();

//        $table->unique(['handle', 'parent_id']);

        $table->timestamps();
    }

    /**
     * @param \SuperV\Platform\Domains\Database\Schema\Blueprint    $table
     * @param \SuperV\Platform\Domains\Resource\ResourceConfig|null $config
     */
    public static function activities($table, ResourceConfig $config = null)
    {
        $table->increments('id');
        if ($table instanceof Blueprint) {
            $config->nav('acp.platform.system');
            $config->label('Resource Activity');

            $table->belongsTo('sv.platform.resources', 'resource')->showOnIndex();
            $table->belongsTo('users', 'user')->showOnIndex();
            $table->nullableMorphTo('entry')->showOnIndex();
            $table->dictionary('payload');

            $table->restorable();
        } else {
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('resource_id');
            $table->unsignedInteger('deleted_by_id')->nullable();
            $table->nullableMorphs('entry');
            $table->text('payload')->nullable();

            $table->softDeletes();
        }

        $table->string('activity')->entryLabel();
        $table->timestamp('created_at')->nullable()->showOnIndex();
    }
}
