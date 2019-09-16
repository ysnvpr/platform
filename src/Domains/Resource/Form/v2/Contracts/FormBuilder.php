<?php

namespace SuperV\Platform\Domains\Resource\Form\v2\Contracts;

use SuperV\Platform\Domains\Resource\Form\Contracts\FormField;
use SuperV\Platform\Domains\Resource\Form\FormModel;

interface FormBuilder
{
    public function setFormEntry(FormModel $formEntry): FormBuilder;

    public function getForm(): Form;

    public function addField(FormField $field);

    public function build();

    public function getFormIdentifier(): string;

    public function setFormIdentifier($formIdentifier): FormBuilder;

    public function setFormUrl($formUrl): FormBuilder;

    public function setFormData($data): FormBuilder;
}
