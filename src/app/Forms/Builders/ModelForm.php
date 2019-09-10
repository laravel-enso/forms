<?php

namespace App\Forms\Builders;

use LaravelEnso\Forms\app\Services\Form;

class ModelForm
{
    protected const FormPath = __DIR__.'/../Templates/template.json';

    protected $form;

    public function __construct()
    {
        $this->form = new Form(static::FormPath);
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
