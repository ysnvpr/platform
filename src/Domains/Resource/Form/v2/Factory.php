<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormBuilder;

class Factory
{
    public static function createBuilder(): FormBuilder
    {
        $builder = app()->make(FormBuilder::class);

        return $builder;
    }
}
