<?php namespace SuperV\Platform\Domains\UI\Form;

use Illuminate\View\Factory;
use SuperV\Platform\Domains\Entry\EntryModel;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Domains\UI\Form\Features\BuildForm;
use SuperV\Platform\Domains\UI\Form\Features\MakeForm;
use Symfony\Component\Form\FormInterface;

class FormBuilder
{
    use ServesFeaturesTrait;

    /** @var  EntryModel */
    protected $entry;

    /** @var  Form */
    protected $form;

    /** @var  FormInterface */
    protected $factory;

    /**
     * @var Factory
     */
    private $view;

    public function __construct(Form $form, Factory $view)
    {
        $this->form = $form;
        $this->view = $view;
    }

    public function build()
    {
        $this->serve(new BuildForm($this));
    }

    public function make()
    {
        $this->build();

        $this->serve(new MakeForm($this));

        $this->post();
    }

    public function post()
    {
        if (app('request')->isMethod('post')) {

            $this->form->handleRequest();

            if ($this->form->isSubmitted() && $this->form->isValid()) {
                $this->entry->save();

                $perms = $this->form->getFormData('permissions');
                $this->entry->permissions()->sync($perms);

                return redirect('/')->withSuccess('Entry saved!');
            }
        }

        return $this;
    }

    public function render($entry)
    {
        $this->entry = $entry;

        $this->make();

        $response = $this->form->createView();


        return $this->view->make('dash', ['form' => $response]);
    }

    /**
     * @return EntryModel
     */
    public function getEntry(): EntryModel
    {
        return $this->entry;
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }
}