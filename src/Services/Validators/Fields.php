<?php

namespace LaravelEnso\Forms\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Forms\Attributes\Fields as Attributes;
use LaravelEnso\Forms\Exceptions\Template;
use LaravelEnso\Helpers\Services\Obj;

class Fields
{
    private Obj $template;

    public function __construct(Obj $template)
    {
        $this->template = $template;
    }

    public function validate(): void
    {
        $this->template->get('sections')
            ->each(fn ($section) => $this->section($section));
    }

    private function section(Obj $section): void
    {
        $this->format($section);

        $section->get('fields')->each(fn ($field) => $this->field($field));
    }

    private function field(Obj $field): void
    {
        $this->attributes($field)
            ->value($field);

        (new Meta($field))->validate();
    }

    private function format($section): void
    {
        $valid = $section->get('fields') instanceof Obj
            && $section->get('fields')
                ->filter(fn ($field) => ! $field instanceof Obj)
                ->isEmpty();

        if (! $valid) {
            throw Template::invalidFieldsFormat();
        }
    }

    private function attributes($field): self
    {
        $diff = (new Collection(Attributes::List))
            ->diff($field->keys());

        if ($diff->isNotEmpty()) {
            throw Template::missingFieldAttributes($diff->implode('", "'));
        }

        return $this;
    }

    private function value($field): void
    {
        $meta = $field->get('meta');

        if ($meta->get('custom')) {
            return;
        }

        if ($meta->get('type') === 'input' && $meta->get('content') === 'checkbox') {
            if (! is_bool($field->get('value'))) {
                throw Template::invalidCheckboxValue($field->get('name'));
            }

            return;
        }

        if ($meta->get('type') === 'select' && $meta->get('multiple')
            && ! is_array($field->get('value')) && ! is_object($field->get('value'))) {
            throw Template::invalidSelectValue($field->get('name'));
        }
    }
}
