<?php

namespace LaravelEnso\Forms\app\Services\Validators;

use LaravelEnso\Enums\app\Services\Enum;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Forms\app\Exceptions\TemplateException;
use LaravelEnso\Forms\app\Attributes\Meta as Attributes;

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
        if ($this->meta->get('custom')) {
            return;
        }

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
            throw TemplateException::missingMetaAttributes(
                $this->field->get('name'), $diff->implode('", "')
            );
        }

        return $this;
    }

    private function checkOptionalAttributes()
    {
        $attributes = collect(Attributes::Mandatory)
            ->merge(Attributes::Optional);

        $diff = $this->meta->keys()
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw TemplateException::unknownMetaAttributes(
                $this->field->get('name'), $diff->implode('", "')
            );
        }

        return $this;
    }

    private function checkFormat()
    {
        if ($this->meta->get('type') === 'input') {
            if (self::inputMetaParameterMissing($this->field)) {
                throw TemplateException::missingInputAttribute($this->field->geT('name'));
            }

            return $this;
        }

        if ($this->meta->get('type') === 'select') {
            if (self::selectMetaParameterMissing($this->field)) {
                throw TemplateException::missingSelectMetaAttribute($this->field->get('name'));
            }

            $options = $this->meta->get('options');

            if ($options && ! is_array($options)
                && ! (is_string($options) && class_exists($options) && new $options instanceof Enum)
                && ! method_exists($options, 'toArray')) {
                throw TemplateException::invalidSelectOptions($this->field->get('name'));
            }
        }

        return $this;
    }

    private function checkType()
    {
        if (! collect(Attributes::Types)->contains($this->meta->get('type'))) {
            throw TemplateException::invalidFieldType($this->meta->get('type'));
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
}
