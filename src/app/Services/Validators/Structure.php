<?php

namespace LaravelEnso\Forms\app\Services\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Forms\app\Exceptions\TemplateValueException;
use LaravelEnso\Forms\app\Attributes\Structure as Attributes;
use LaravelEnso\Forms\app\Exceptions\TemplateFormatException;
use LaravelEnso\Forms\app\Exceptions\TemplateAttributeException;

class Structure
{
    private $template;

    public function __construct(Obj $template)
    {
        $this->template = $template;
    }

    public function validate()
    {
        $this->checkRootMandatoryAttributes()
            ->checkRootOptionalAttributes()
            ->checkRootAttributesFormat()
            ->checkSections()
            ->checkTabs();
    }

    private function checkRootMandatoryAttributes()
    {
        $diff = collect(Attributes::Mandatory)
            ->diff($this->template->keys());

        if ($diff->isNotEmpty()) {
            throw new TemplateAttributeException(__(
                'Mandatory attribute(s) missing: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function checkRootOptionalAttributes()
    {
        $attributes = collect(Attributes::Mandatory)
            ->merge(Attributes::Optional);

        $diff = $this->template->keys()
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw new TemplateAttributeException(__(
                'Unknown attribute(s) found: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function checkRootAttributesFormat()
    {
        if ($this->template->has('actions')
            && ! $this->template->get('actions') instanceof Obj) {
            throw new TemplateFormatException(__('"actions" attribute must be an array'));
        }

        if ($this->template->has('params')
            && ! $this->template->get('params') instanceof Obj) {
            throw new TemplateFormatException(__('"params" attribute must be an object'));
        }

        if (! $this->template->get('sections') instanceof Obj) {
            throw new TemplateFormatException(__('"section" attribute must be an array'));
        }

        return $this;
    }

    private function checkSections()
    {
        $attributes = $this->template->get('sections')
            ->reduce(function ($attributes, $section) {
                return $attributes->merge($section->keys());
            }, collect())->unique()->values();

        $this->checkSectionsMandatory($attributes)
            ->checkSectionsOptional($attributes)
            ->checkColumnsFormat();

        return $this;
    }

    private function checkSectionsMandatory($attributes)
    {
        $diff = collect(Attributes::SectionMandatory)
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw new TemplateAttributeException(__(
                'Mandatory attribute(s) missing from section object: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function checkSectionsOptional($attributes)
    {
        $diff = $attributes->diff(
            collect(Attributes::SectionMandatory)
                ->merge(Attributes::SectionOptional)
        );

        if ($diff->isNotEmpty()) {
            throw new TemplateAttributeException(__(
                'Unknown attribute(s) found in section object: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function checkColumnsFormat()
    {
        $this->template->get('sections')
            ->each(function ($section) {
                if (! collect(Attributes::Columns)->contains($section->get('columns'))) {
                    throw new TemplateValueException(__(
                        'Invalid "columns" value found in section object: :columns. Allowed values are: :allowed', [
                            'columns' => $section->get('columns'),
                            'allowed' => collect(Attributes::Columns)->implode(', '),
                        ]
                    ));
                }

                if ($section->get('columns') === 'custom') {
                    $this->checkCustomColumns($section);
                }
            });
    }

    private function checkCustomColumns($section)
    {
        $section->get('fields')
            ->each(function ($field) {
                if (! $field->has('column')) {
                    throw new TemplateValueException(__(
                        'Missing "column" attribute from the field: ":field". This is mandatory when using custom columns on a section.',
                        ['field' => $field->get('name')]
                    ));
                }

                if (! is_int($field->get('column'))
                    || $field->get('column') <= 0
                    || $field->get('column') > 12) {
                    throw new TemplateValueException(__(
                        'Invalid "column" value found for field: :field. Allowed values from 1 to 12',
                        ['field' => $field->get('name')]
                    ));
                }
            });
    }

    private function checkTabs()
    {
        if (! $this->template->get('tabs')) {
            return;
        }

        $diff = $this->template->get('sections')
            ->filter(function ($section) {
                return ! $section->has('tab');
            });

        if ($diff->isNotEmpty()) {
            throw new TemplateFormatException(__(
                '"tab" attribute is missing on the following columns :columns',
                ['columns' => $diff->keys()->implode('", "')]
            ));
        }
    }
}
