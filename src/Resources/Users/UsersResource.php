<?php

namespace SuperV\Platform\Resources\Users;

use SuperV\Platform\Domains\Resource\Hook\Contracts\ResourceResolvedHook;
use SuperV\Platform\Domains\Resource\Resource;

class UsersResource implements ResourceResolvedHook
{
    public static $identifier = 'platform.users';

    public function resolved(Resource $resource)
    {
        $resource->registerAction('update_password', UpdatePasswordAction::class);
    }
}
