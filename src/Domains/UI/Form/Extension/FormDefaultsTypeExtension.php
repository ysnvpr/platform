<?php namespace SuperV\Platform\Domains\UI\Form\Extension;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormDefaultsTypeExtension extends AbstractTypeExtension
{
    /** @var array  */
    protected $defaults;

    public function __construct($defaults)
    {
        $this->defaults = $defaults;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        foreach ($this->defaults as $key => $default) {
            $resolver->setDefault($key, $default);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FormType::class;
    }
}
