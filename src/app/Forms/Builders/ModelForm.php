<?php

namespace App\Forms\Builders;

use LaravelEnso\FormBuilder\app\Classes\Form;

class ModelForm
{
    private const FormPath = __DIR__.'/../Templates/template.json';

    private $form;

    public function __construct()
    {
        $this->form = new Form(self::FormPath);
    }

    public function create()
    {
        return $this->form->create();
    }

    public function edit(Model $model)
    {
        return $this->form->edit($model);
    }
}
