<?php

namespace LaravelEnso\Forms\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Forms\Attributes\Structure as Attributes;
use LaravelEnso\Forms\Exceptions\Template;
use LaravelEnso\Helpers\Services\Obj;

class Structure
{
    public function __construct(private Obj $template)
    {
    }

    public function validate()
    {
        $this->rootMandatoryAttributes()
            ->rootOptionalAttributes()
            ->rootAttributesFormat()
            ->sections()
            ->tabs();
    }

    private function rootMandatoryAttributes(): self
    {
        $diff = Collection::wrap(Attributes::Mandatory)
            ->diff($this->template->keys());

        if ($diff->isNotEmpty()) {
            throw Template::missingRootAttributes($diff->implode('", "'));
        }

        return $this;
    }

    private function rootOptionalAttributes(): self
    {
        $attributes = Collection::wrap(Attributes::Mandatory)
            ->merge(Attributes::Optional);

        $diff = $this->template->keys()
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw Template::unknownRootAttributes($diff->implode('", "'));
        }

        return $this;
    }

    private function rootAttributesFormat(): self
    {
        $invalidActions = $this->template->has('actions')
            && ! $this->template->get('actions') instanceof Obj;

        if ($invalidActions) {
            throw Template::invalidActionsFormat();
        }

        $invalidParams = $this->template->has('params')
            && ! $this->template->get('params') instanceof Obj;

        if ($invalidParams) {
            throw Template::invalidParamsFormat();
        }

        if (! $this->template->get('sections') instanceof Obj) {
            throw Template::invalidSectionFormat();
        }

        return $this;
    }

    private function sections(): self
    {
        $attributes = $this->template->get('sections')
            ->reduce(fn ($attributes, $section) => $attributes
                ->merge($section->keys()), new Collection())
            ->unique()->values();

        $this->sectionsMandatory($attributes)
            ->sectionsOptional($attributes)
            ->sectionsColumnFormat();

        return $this;
    }

    private function sectionsMandatory(Collection $attributes): self
    {
        $diff = Collection::wrap(Attributes::SectionMandatory)
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw Template::missingSectionAttributes($diff->implode('", "'));
        }

        return $this;
    }

    private function sectionsOptional(Collection $attributes): self
    {
        $diff = $attributes
            ->diff(Collection::wrap(Attributes::SectionMandatory)
                ->merge(Attributes::SectionOptional));

        if ($diff->isNotEmpty()) {
            throw Template::unknownSectionAttributes($diff->implode('", "'));
        }

        return $this;
    }

    private function sectionsColumnFormat(): void
    {
        $this->template->get('sections')
            ->each(fn ($section) => $this->sectionColumnsFormat($section));
    }

    private function sectionColumnsFormat(Obj $section): void
    {
        if ($this->invalidColumn($section)) {
            throw Template::invalidColumnsAttributes(
                $section->get('columns'),
                implode(', ', Attributes::Columns)
            );
        }

        if ($section->get('columns') === 'custom') {
            $section->get('fields')
                ->each(fn ($field) => $this->checkCustomColumns($field));
        }
    }

    private function checkCustomColumns(Obj $field): void
    {
        if (! $field->has('column')) {
            throw Template::missingFieldColumn($field->get('name'));
        }

        $invalidColumn = ! is_int($field->get('column'))
            || $field->get('column') <= 0
            || $field->get('column') > 12;

        if ($invalidColumn) {
            throw Template::invalidFieldColumn($field->get('name'));
        }
    }

    private function tabs(): void
    {
        if (! $this->template->get('tabs')) {
            return;
        }

        $diff = $this->template->get('sections')
            ->filter(fn ($section) => ! $section->has('tab'));

        if ($diff->isNotEmpty()) {
            throw Template::missingTabAttribute($diff->keys()->implode('", "'));
        }
    }

    private function invalidColumn(Obj $section): bool
    {
        return (! is_numeric($section->get('columns')) || $section->get('columns') <= 0)
            && ! in_array($section->get('columns'), Attributes::Columns);
    }
}
