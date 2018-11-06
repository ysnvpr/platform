<?php

use SuperV\Platform\Domains\Auth\Access\Action;
use SuperV\Platform\Domains\Auth\Access\Role;
use SuperV\Platform\Domains\Auth\Account;
use SuperV\Platform\Domains\Auth\Profile;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Database\Migrations\Migration;

class CreateUsersPrototype extends Migration
{
    public function up()
    {
        Schema::run('users', function (Blueprint $table) {
            $table->increments('id');
            $table->belongsTo(Account::class, 'account')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('remember_token')->nullable();
            $table->timestamps();

            $table->hasOne(Profile::class, 'profile', 'user_id');
            $table->morphToMany(Role::class, 'roles', 'owner', 'auth_assigned_roles', 'role_id');

            $pivotColumns = function (Blueprint $pivotTable) {
                $pivotTable->select('provision')->options(['pass' => 'Pass', 'fail' => 'Fail']);
            };
            $table->morphToMany(Action::class, 'actions', 'owner', 'auth_assigned_actions', 'action_id', $pivotColumns);
        });
    }

    public function down()
    {
    }
}