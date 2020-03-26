<?php

namespace LaravelEnso\Forms\App\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Enums\App\Services\Enum;
use LaravelEnso\Forms\App\Attributes\Meta as Attributes;
use LaravelEnso\Forms\App\Exceptions\Template;
use LaravelEnso\Helpers\App\Classes\Obj;

class Meta
{
    private Obj $field;
    private ?Obj $meta;

    public function __construct(Obj $field)
    {
        $this->field = $field;
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
        $diff = (new Collection(Attributes::Mandatory))
            ->diff($this->meta->keys());

        if ($diff->isNotEmpty()) {
            throw Template::missingMetaAttributes(
                $this->field->get('name'), $diff->implode('", "')
            );
        }

        return $this;
    }

    private function optionalAttributes(): self
    {
        $attributes = (new Collection(Attributes::Mandatory))
            ->merge(Attributes::Optional);

        $diff = $this->meta->keys()
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw Template::unknownMetaAttributes(
                $this->field->get('name'), $diff->implode('", "')
            );
        }

        return $this;
    }

    private function format(): self
    {
        if ($this->meta->get('type') === 'input') {
            if ($this->inputMetaParameterMissing($this->field)) {
                throw Template::missingInputAttribute($this->field->geT('name'));
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
        if (! (new Collection(Attributes::Types))->contains($this->meta->get('type'))) {
            throw Template::invalidFieldType($this->meta->get('type'));
        }
    }

    private function inputMetaParameterMissing(): bool
    {
        return $this->meta === null || ! $this->meta->has('content');
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
