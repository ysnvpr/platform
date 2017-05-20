<?php namespace SuperV\Platform\Domains\Model;


use Illuminate\Database\Eloquent\Model;

class EloquentModel extends Model
{
    protected $guarded = [];
    
    public $timestamps = false;

    public function getTable()
    {
        if (! isset($this->table)) {
            return str_replace('\\', '', Str::snake(Str::plural(class_basename($this))));
        }

        return $this->table;
    }
    
    public function getId()
    {
        return $this->getKey();
    }
}