<?php

namespace Tests\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Resource\Form\FormField;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FieldComposer;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\v2\Jobs\ComposeForm;
use SuperV\Platform\Support\Composer\Payload;
use Tests\Platform\Domains\Resource\Form\v2\Helpers\FormFake;
use Tests\Platform\Domains\Resource\Form\v2\Helpers\FormTestHelpers;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ComposeFormTest extends ResourceTestCase
{
    use FormTestHelpers;

    function test__compose()
    {
        $this->app->bind(FormInterface::class, FormFake::class);
        $fieldComposerMock = $this->bindMock(FieldComposer::class);

        /** @var FormFake $form */
        $form = app(FormInterface::class);

        $form->setFakeFields(['field-1', 'field-2' => 'email', 'field-3' => ['type' => 'number']]);
        $form->getFields()->map(function (FormField $field) use ($form, $fieldComposerMock) {
            $fieldComposerMock->shouldReceive('toForm')
                              ->withArgs(function (FormInterface $formArg, FormField $fieldArg) use ($form, $field) {
                                  return $fieldArg->getIdentifier() === $field->getIdentifier()
                                      && $formArg->getIdentifier() === $form->getIdentifier();
                              })
                              ->andReturn(['composed-'.$field->getIdentifier()])
                              ->once();
        });

        $payload = $form->compose();

        $this->assertInstanceOf(Payload::class, $payload);

        $this->assertEquals([
            'identifier' => 'form-id',
            'url'        => 'url-to-form',
            'method'     => 'PATCH',
            'fields'     => [
                ['composed-field-1'],
                ['composed-field-2'],
                ['composed-field-3'],
            ],
        ], $payload->get());
    }

    function test__composes_entry_ids()
    {
        $form = FormFake::fake();

        $partialForm = \Mockery::mock($form)->makePartial();

        $partialForm->shouldReceive('getEntryIds')->andReturn(['ns.a' => 1, 'ns.b' => 2])->once();

        $payload = ComposeForm::resolve()->handle($partialForm);

        $this->assertEquals(['ns.a' => 1, 'ns.b' => 2], $payload->get('entries'));
    }
}


