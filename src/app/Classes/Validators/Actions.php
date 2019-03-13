<?php

namespace LaravelEnso\FormBuilder\app\Classes\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\FormBuilder\app\Exceptions\TemplateException;
use LaravelEnso\FormBuilder\app\Classes\Attributes\Actions as Attributes;

class Actions
{
    private $template;

    public function __construct(Obj $template)
    {
        $this->template = $template;
    }

    public function validate()
    {
        $diff = collect($this->template->get('actions'))
            ->diff(collect(Attributes::List));

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Incorrect action(s) provided: :actions. Allowed actions are: :actionList', [
                    'actions' => $diff->implode(', '),
                    'actionList' => collect(Attributes::List)->implode(', '),
                ]
            ));
        }
    }
}
