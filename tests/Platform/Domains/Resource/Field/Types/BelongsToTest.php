<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use Closure;
use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Types\BelongsTo;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class BelongsToTest extends ResourceTestCase
{
    function test__creates_field_type()
    {
        $this->makeGroupResource();

        $users = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->entryLabel();
            $table->belongsTo('t_groups', 'group');
        });

        $this->assertColumnExists('t_users', 'group_id');
        $belongsTo = $users->fake(['group_id' => 100])->getFieldType('group');

        $this->assertInstanceOf(BelongsTo::class, $belongsTo);
        $this->assertEquals('belongs_to', $belongsTo->getType());
        $this->assertEquals('t_groups', $belongsTo->getConfigValue('related_resource'));
        $this->assertEquals('group_id', $belongsTo->getConfigValue('foreign_key'));

        $belongsTo->build();
        $this->assertEquals([
            ['value' => 100, 'text' => 'Users'],
            ['value' => 110, 'text' => 'Admins'],
        ], $belongsTo->getConfigValue('options'));
    }

    function test__presenting_callback()
    {
        $this->makeGroupResource();
        $users = $this->create('t_users',
            function (Blueprint $table, ResourceBlueprint $resource) {
                $resource->model(BelongsToTestUser::class);
                $table->increments('id');
                $table->string('name')->entryLabel();
                $table->belongsTo('t_groups', 'group');
            });

        $fakeUser = $users->fake(['group_id' => 100]);
        $belongsTo = $fakeUser->getFieldType('group');

        $callback = $belongsTo->getPresentingCallback();
        $this->assertInstanceOf(Closure::class, $callback);

        $this->assertEquals($fakeUser->name, $callback($fakeUser));
    }

    protected function makeGroupResource(): void
    {
        $groups = $this->create('t_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->entryLabel();
        });

        $groups->create(['id' => 100, 'title' => 'Users']);
        $groups->create(['id' => 110, 'title' => 'Admins']);
    }
}

class BelongsToTestUser extends Model implements EntryContract
{
    public $timestamps = false;

    protected $table = 't_users';

    protected $guarded = [];

    public function id()
    {
        return $this->getKey();
    }
}