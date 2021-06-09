<?php

namespace LaravelEnso\Forms\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Enums\Services\Enum;
use LaravelEnso\Forms\Attributes\Meta as Attributes;
use LaravelEnso\Forms\Exceptions\Template;
use LaravelEnso\Helpers\Services\Obj;

class Meta
{
    private ?Obj $meta;

    public function __construct(private Obj $field)
    {
        $this->meta = $field->get('meta');
    }

    public function validate(): void
    {
        if ($this->meta->get('custom')) {
            return;
        }

        $this->mandatoryAttributes()
            ->optionalAttributes()
            ->format()
            ->type();
    }

    private function mandatoryAttributes(): self
    {
        $diff = Collection::wrap(Attributes::Mandatory)
            ->diff($this->meta->keys());

        if ($diff->isNotEmpty()) {
            throw Template::missingMetaAttributes(
                $this->field->get('name'),
                $diff->implode('", "')
            );
        }

        return $this;
    }

    private function optionalAttributes(): self
    {
        $attributes = Collection::wrap(Attributes::Mandatory)
            ->merge(Attributes::Optional);

        $diff = $this->meta->keys()
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw Template::unknownMetaAttributes(
                $this->field->get('name'),
                $diff->implode('", "')
            );
        }

        return $this;
    }

    private function format(): self
    {
        if ($this->meta->get('type') === 'input') {
            if (! $this->meta->has('content')) {
                throw Template::missingInputContent($this->field->get('name'));
            }

            return $this;
        }

        if ($this->meta->get('type') === 'select') {
            if ($this->selectMetaParameterMissing($this->field)) {
                throw Template::missingSelectMetaAttribute($this->field->get('name'));
            }

            if ($this->invalidOptions($this->meta->get('options'))) {
                throw Template::invalidSelectOptions($this->field->get('name'));
            }
        }

        return $this;
    }

    private function type(): void
    {
        if (! in_array($this->meta->get('type'), Attributes::Types)) {
            throw Template::invalidFieldType($this->meta->get('type'));
        }
    }

    private function selectMetaParameterMissing(): bool
    {
        return $this->meta === null
            || (! $this->meta->has('options') && ! $this->meta->has('source'));
    }

    private function invalidOptions($options)
    {
        return $options && ! is_array($options)
            && ! (is_string($options) && class_exists($options)
                && new $options() instanceof Enum)
            && ! method_exists($options, 'toArray');
    }
}
