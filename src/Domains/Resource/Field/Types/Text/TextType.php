<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Text;

use SuperV\Platform\Domains\Resource\Contracts\ProvidesFilter;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesFieldComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\Contracts\SortsQuery;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Filter\DistinctFilter;

class TextType extends FieldType implements
    RequiresDbColumn,
    ProvidesFilter,
    ProvidesFieldComponent,
    SortsQuery
{
    protected $component = 'sv_text_field';

    protected $handle = 'text';

    public function makeRules()
    {
        if ($length = $this->getConfigValue('length')) {
            return ["max:{$length}"];
        }
    }

    public function makeFilter(?array $params = [])
    {
        return DistinctFilter::make($this->getFieldHandle());
    }

    public function sortQuery($query, $direction)
    {
        $query->orderBy($this->field->getColumnName(), $direction);
    }

    public function driverCreating(
        DriverInterface $driver,
        \SuperV\Platform\Domains\Resource\Builder\FieldBlueprint $blueprint
    ) {
        if ($driver instanceof DatabaseDriver) {
            $driver->getTable()->addColumn($this->getColumnName(), 'string');
        }
    }

    public function getComponentName(): string
    {
        return $this->component;
    }
}
