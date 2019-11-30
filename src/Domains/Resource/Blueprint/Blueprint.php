<?php

namespace SuperV\Platform\Domains\Resource\Blueprint;

use Illuminate\Support\Collection;
use Psy\Exception\RuntimeException;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Relation\RelationType;

class Blueprint
{
    use FieldHelpers;
    use RelationHelpers;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $handle;

    /**
     * @var string
     */
    protected $label;

    protected $nav;

    /**
     * @var \SuperV\Platform\Domains\Resource\Driver\DriverInterface
     */
    protected $driver;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $fields;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $relations;

    public function __construct()
    {
        $this->fields = collect();
        $this->relations = collect();
    }

    public function getRelations(): Collection
    {
        return $this->relations;
    }

    public function id()
    {
        return $this->primaryKey(...func_get_args());
    }

    public function primaryKey($name = 'id', $type = 'integer', array $options = []): self
    {
        $this->getDriver()->primaryKey($name, $type, $options);

        return $this;
    }

    public function addRelation(string $relatedResource, string $relationName, RelationType $relationType)
    {
        $relationBlueprint = RelationBlueprint::make($this, $relationName, $relationType);
        $relationBlueprint->relatedResource($relatedResource);

        $this->relations->put($relationName, $relationBlueprint);

        return $relationBlueprint;
    }

    public function getRelation($relationName): RelationBlueprint
    {
        return $this->relations->get($relationName);
    }

    public function addField($fieldName, $fieldTypeClass, string $label = null): FieldBlueprint
    {
        $fieldBlueprint = FieldBlueprint::make($this, $fieldName, $fieldTypeClass);
        $fieldBlueprint->label($label);

        $this->fields->put($fieldName, $fieldBlueprint);

        return $fieldBlueprint;
    }

    public function getField($fieldName): ?FieldBlueprint
    {
        return $this->fields->get($fieldName);
    }

    public function getFieldRules($fieldName): array
    {
        return $this->getField($fieldName)->getRules();
    }

    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function namespace($namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function handle($handle): self
    {
        $this->handle = $handle;

        return $this;
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function nav($nav): self
    {
        $this->nav = $nav;

        return $this;
    }

    public function getIdentifier()
    {
        return $this->getNamespace().'.'.$this->getHandle();
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function driver(DriverInterface $driver): Blueprint
    {
        $this->driver = $driver;

        return $this;
    }

    public function databaseDriver(): DatabaseDriver
    {
        return $this->driver = DatabaseDriver::resolve();
    }

    public function getDriver(): ?DriverInterface
    {
        if (! $this->driver) {
            $this->driver = $this->resolveDefaultDriver();
        }

        return $this->driver;
    }

    public function getNav()
    {
        return $this->nav;
    }

    protected function resolveDefaultDriver()
    {
        if (! $this->getIdentifier()) {
            throw new RuntimeException('Can not resolve default driver without an identifier');
        }

        return DatabaseDriver::resolve()->setParam('table', $this->getHandle());
    }

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}