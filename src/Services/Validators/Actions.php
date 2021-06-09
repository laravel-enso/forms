<?php

namespace LaravelEnso\Forms\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Forms\Attributes\Actions as Attributes;
use LaravelEnso\Forms\Exceptions\Template;
use LaravelEnso\Helpers\Services\Obj;

class Actions
{
    public function __construct(private Obj $template)
    {
    }

    public function validate(): void
    {
        $attributes = Collection::wrap(Attributes::Create)
            ->merge(Attributes::Update)
            ->unique()
            ->values();

        $diff = $this->template->get('actions')
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw Template::unknownActions(
                $diff->implode(', '),
                $attributes->implode(', ')
            );
        }
    }
}
