<?php

namespace LaravelEnso\Forms\app\Services\Validators;

use LaravelEnso\Forms\app\Attributes\Actions as Attributes;
use LaravelEnso\Forms\app\Exceptions\TemplateException;
use LaravelEnso\Helpers\app\Classes\Obj;

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

        $diff = $this->template->get('actions')
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw TemplateException::unknownActions(
                $diff->implode(', '), $attributes->implode(', ')
            );
        }
    }
}
