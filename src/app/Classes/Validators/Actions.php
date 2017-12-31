<?php

namespace LaravelEnso\FormBuilder\app\Classes\Validators;

use LaravelEnso\FormBuilder\app\Classes\Attributes\Actions as Attributes;
use LaravelEnso\FormBuilder\app\Exceptions\TemplateException;

class Actions
{
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function validate()
    {
        if (!isset($this->template->actions)) {
            $this->template->actions = $this->defaultActions();

            return;
        }

        $diff = collect($this->template->actions)->diff(collect(Attributes::List));

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(sprintf(
                'Incorrect action(s) provided "%s". Allowed actions are: "create", "store", "update" and "delete"',
                $diff->explode(', ')
            )));
        }
    }

    private function defaultActions()
    {
        return $this->template->method === 'post'
            ? ['store']
            : ['create', 'update', 'destroy'];
    }
}
