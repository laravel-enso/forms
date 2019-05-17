<?php

namespace LaravelEnso\Forms\app\Services\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Forms\app\Exceptions\TemplateException;
use LaravelEnso\Forms\app\Attributes\Actions as Attributes;

class Actions
{
    private $template;

    public function __construct(Obj $template)
    {
        $this->template = $template;
    }

    public function validate()
    {
        $attributes = collect(Attributes::Create)
            ->merge(Attributes::Update)
            ->unique()
            ->values();

        $diff = collect($this->template->get('actions'))
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Incorrect action(s) provided: :actions. Allowed actions are: :actionList', [
                    'actions' => $diff->implode(', '),
                    'actionList' => $attributes->implode(', '),
                ]
            ));
        }
    }
}
