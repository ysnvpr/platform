<?php namespace SuperV\Platform\Domains\Manifest;

class ModelManifest extends Manifest
{
    protected $model;


    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     *
     * @return Manifest
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

}