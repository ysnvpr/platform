<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use Exception;
use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceEntryModel;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\HasConfig;
use SuperV\Platform\Support\Concerns\Hydratable;

abstract class FieldType
{
    use Hydratable;
    use HasConfig;
    use FiresCallbacks;

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\FieldModel
     */
    protected $entry;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var boolean
     */
    protected $built = false;

    public function __construct(FieldModel $entry)
    {
        $this->entry = $entry;
    }

    public function show(): bool
    {
        return true;
    }

    public function build(): self
    {
        if ($this->isBuilt()) {
            throw new Exception('Field is already built');
        }
        $this->built = true;

        return $this;
    }

    public function compose(): array
    {
        if (! $this->isBuilt()) {
            throw new Exception('Field is not built yet');
        }

        return array_filter([
            'uuid'   => $this->uuid(),
            'name'   => $this->getColumnName(),
            'label'  => $this->getLabel(),
            'type'   => $this->getType(),
            'config' => $this->getConfig(),
            'value'  => $this->getValue(),
        ]);
    }

    public function isBuilt(): bool
    {
        return $this->built;
    }

    public function uuid()
    {
        return $this->entry->uuid();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getColumnName(): ?string
    {
        return $this->name;
    }

    public function getLabel()
    {
        return $this->label ?: ucwords(str_replace('_', ' ', $this->name));
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEntry(): ?FieldModel
    {
        return $this->entry;
    }

    public function mergeConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    public function getResource(): Resource
    {
        return $this->resource;
    }

    public function setResource(Resource $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function setRules(array $rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    public function mergeRules(array $rules)
    {
        $this->rules = array_merge($this->rules, $rules);
    }

    public function getValue()
    {
        if (! $this->resourceExists()) {
            return null;
        }

        $value = $this->getResourceEntry()->getAttribute($this->getColumnName());

        if ($accessor = $this->getAccessor()) {
            return $accessor($value);
        }

        return $value;
    }

    public function setValue($value): ?Closure
    {
        if ($mutator = $this->getMutator()) {
            $value = $mutator($value);
        }

        $this->getResourceEntry()->setAttribute($this->getColumnName(), $value);

        return null;
    }

    public function setValueFromRequest(Request $request)
    {
        return $this->setValue($request->__get($this->getColumnName()));
    }
    public function resourceExists(): bool
    {
        return $this->resource && $this->resource->getEntryId();
    }

    public function getResourceEntry(): ?ResourceEntryModel
    {
        return $this->resource ? $this->resource->getEntry() : null;
    }

    public function getAccessor(): ?Closure
    {
        return null;
    }

    public function setAccessor(Closure $accessor)
    {
        $this->accessor = $accessor;

        return $this;
    }

    public function getMutator(): ?Closure
    {
        return null;
    }

    public static function make($name): self
    {
        return static::fromEntry(new FieldModel(['name' => $name]));
    }

    public static function fromEntry(FieldModel $entry): self
    {
        $field = new static($entry);

        $field->hydrate($entry->toArray());

        return $field;
    }
}