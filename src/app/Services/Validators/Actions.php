<?php

namespace LaravelEnso\Forms\App\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Forms\App\Attributes\Actions as Attributes;
use LaravelEnso\Forms\App\Exceptions\Template;
use LaravelEnso\Helpers\App\Classes\Obj;

class Actions
{
    private Obj $template;

    public function __construct(Obj $template)
    {
        $this->template = $template;
    }

    public function validate(): void
    {
        $attributes = (new Collection(Attributes::Create))
            ->merge(Attributes::Update)
            ->unique()
            ->values();

        $diff = $this->template->get('actions')
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw Template::unknownActions(
                $diff->implode(', '), $attributes->implode(', ')
            );
        }
    }
}
