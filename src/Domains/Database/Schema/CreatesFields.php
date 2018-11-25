<?php

namespace SuperV\Platform\Domains\Database\Schema;

use SuperV\Platform\Domains\Media\MediaOptions;

/**
 * Trait CreatesFields
 * @method ColumnDefinition string($column, $length = null)
 * @method ColumnDefinition unsignedInteger($column, $autoIncrement = false)
 */
trait CreatesFields
{
    public function email($name): ColumnDefinition
    {
        return $this->string($name)->fieldType('email');
    }

    public function file($name, $path = '/', $disk = 'public'): ColumnDefinition
    {
        return $this->addColumn(null, $name)->fieldType('file')->ignore()->nullable()
                    ->config(MediaOptions::one($name)
                                         ->public()
                                         ->disk($disk)
                                         ->path($path)
                                         ->all());
    }

    public function select($name): ColumnDefinition
    {
        return $this->string($name)->fieldType('select');
    }
}