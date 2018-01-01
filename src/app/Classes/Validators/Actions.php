<?php

namespace LaravelEnso\FormBuilder\app\Classes\Validators;

use LaravelEnso\FormBuilder\app\Exceptions\TemplateException;
use LaravelEnso\FormBuilder\app\Classes\Attributes\Actions as Attributes;

class Actions
{
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function validate()
    {
        $diff = collect($this->template->actions)->diff(collect(Attributes::List));

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(sprintf(
                'Incorrect action(s) provided "%s". Allowed actions are: "create", "store", "update" and "delete"',
                $diff->explode(', ')
            )));
        }
    }
}
