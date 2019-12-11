<?php

namespace LaravelEnso\Forms\app\Services\Validators;

use LaravelEnso\Enums\app\Services\Enum;
use LaravelEnso\Forms\app\Attributes\Meta as Attributes;
use LaravelEnso\Forms\app\Exceptions\Template;
use LaravelEnso\Helpers\app\Classes\Obj;

class Meta
{
    private $field;
    private $meta;

    public function __construct(Obj $field)
    {
        $this->field = $field;
        $this->meta = $field->get('meta');
    }

    public function validate()
    {
        $this->checkMandatoryAttributes()
            ->checkOptionalAttributes()
            ->checkFormat()
            ->checkType();
    }

    private function checkMandatoryAttributes()
    {
        $diff = collect(Attributes::Mandatory)
            ->diff($this->meta->keys());

        if ($diff->isNotEmpty()) {
            throw Template::missingMetaAttributes(
                $this->field->get('name'), $diff->implode('", "')
            );
        }

        return $this;
    }

    private function checkOptionalAttributes()
    {
        $attributes = collect(Attributes::Mandatory)
            ->merge(Attributes::Optional);

        $diff = $this->meta->keys()->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw Template::unknownMetaAttributes(
                $this->field->get('name'), $diff->implode('", "')
            );
        }

        return $this;
    }

    private function checkFormat()
    {
        if ($this->meta->get('type') === 'input') {
            $this->validateInputMeta();

            return $this;
        }

        if ($this->meta->get('type') === 'select') {
            $this->validateSelectMeta();

            $options = $this->meta->get('options');
            $this->validateSelectOptions($options);
        }

        return $this;
    }

    private function checkType()
    {
        if (! collect(Attributes::Types)->contains($this->meta->get('type'))) {
            throw Template::invalidFieldType($this->meta->get('type'));
        }
    }

    private function selectMetaParameterMissing()
    {
        return $this->meta === null
            || (! $this->meta->has('options') && ! $this->meta->has('source'));
    }

    private function inputMetaParameterMissing()
    {
        return $this->meta === null || ! $this->meta->has('content');
    }

    private function validateInputMeta(): void
    {
        if ($this->inputMetaParameterMissing($this->field)) {
            throw Template::missingInputAttribute($this->field->geT('name'));
        }
    }

    private function validateSelectMeta(): void
    {
        if ($this->selectMetaParameterMissing($this->field)) {
            throw Template::missingSelectMetaAttribute($this->field->get('name'));
        }
    }

    private function validateSelectOptions($options): void
    {
        if ($options && ! is_array($options)
            && ! (is_string($options) && class_exists($options) && new $options() instanceof Enum)
            && ! method_exists($options, 'toArray')) {
            throw Template::invalidSelectOptions($this->field->get('name'));
        }
    }
}
