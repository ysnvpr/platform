<?php namespace SuperV\Platform\Domains\UI\Form;

class FormAction
{
    private $slug;

    private $type;

    private $options;

    public function __construct($slug, $type, $options)
    {
        $this->slug = $slug;
        $this->type = $type;
        $this->options = $options;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }
}