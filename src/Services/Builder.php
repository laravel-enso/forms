<?php

namespace LaravelEnso\Forms\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use LaravelEnso\Forms\Attributes\Fields;
use LaravelEnso\Helpers\Services\Obj;

class Builder
{
    public function __construct(
        private Obj $template,
        private Collection $dirty,
        private ?Model $model = null
    ) {
    }

    public function run(): void
    {
        $this->appendParams()
            ->values()
            ->computeActions()
            ->computeMetas();

        $this->template->forget(['routes', 'routePrefix', 'authorize']);
    }

    private function values(): self
    {
        if (! $this->model) {
            return $this;
        }

        $this->template->get('sections')
            ->each(fn ($section) => $section->set('hidden', $section->get('hidden', false))
                ->get('fields')->each(fn ($field) => $field
                    ->set('value', $this->value($field))));

        return $this;
    }

    private function value($field)
    {
        $value = $this->dirty->contains($field->get('name'))
            ? $field->get('value')
            : $this->attributeValue($field);

        $meta = $field->get('meta');

        return match ($meta->get('type')) {
            'input' => $this->inputValue($value, $meta),
            'datepicker' => $this->dateValue($value, $meta),
            'select' => $this->selectValue($value, $meta),
            default => $value
        };
    }

    private function inputValue($value, $meta)
    {
        return match ($meta->get('content')) {
            'text' => $value ?? '',
            'encrypt' => isset($value) ? Fields::EncryptValue : null,
            default => $value,
        };
    }

    private function dateValue($value, $meta)
    {
        return $value instanceof Carbon
            ? $value->format($meta->get('format') ?? 'Y-m-d')
            : $value;
    }

    private function selectValue($value, $meta)
    {
        if ($meta->get('objects')) {
            return $value;
        }

        if ($meta->get('multiple')) {
            return $value instanceof Collection
                ? $value->pluck($meta->get('trackBy') ?? 'id')
                : $value;
        }

        return $value instanceof Model
            ? $value->{$meta->get('trackBy') ?? 'id'}
            : $value;
    }

    private function computeMetas(): void
    {
        $this->template->get('sections')
            ->each(fn ($section) => $section->get('fields')
                ->each(fn ($field) => $this->computeMeta($field)));
    }

    private function computeMeta($field)
    {
        $meta = $field->get('meta');

        match ($meta->get('type')) {
            'select' => $this->computeSelect($meta),
            'input' => $this->computeInput($field),
            'datepicker' => $this->computeDate($meta),
            'wysiwyg' => $this->computeWysiwyg($meta),
            default => null,
        };
    }

    private function computeInput($field): void
    {
        if ($field->get('meta')->get('content') === 'encrypt') {
            $field->get('meta')->set('initialValue', $field->get('value'));
        }
    }

    private function computeSelect($meta): void
    {
        if ($meta->has('options') && is_string($meta->get('options'))) {
            $enum = App::make($meta->get('options'));
            $meta->set('options', $enum::select());
        }

        if (! $meta->has('placeholder')) {
            $meta->set('placeholder', config('enso.forms.selectPlaceholder'));
        }

        if (! $meta->has('trackBy')) {
            $meta->set('trackBy', 'id');
        }

        if (! $meta->has('label')) {
            $meta->set('label', 'name');
        }

        if ($meta->has('source')) {
            $meta->set('source', route($meta->get('source'), [], false));
        }
    }

    private function computeDate($meta): void
    {
        $altFormat = $meta->get('altFormat', config('enso.forms.altDateFormat'));
        $meta->set('altFormat', $altFormat);
    }

    private function computeWysiwyg($meta): void
    {
        $meta->set('apiKey', config('enso.forms.tinyMCEApiKey'));
    }

    private function attributeValue($field)
    {
        return Str::contains($field->get('name'), '.')
            ? data_get($this->model, $field->get('name'))
            : $this->model->{$field->get('name')};
    }

    private function computeActions(): self
    {
        $actions = $this->template->get('actions')
            ->reduce(fn ($collector, $action) => $collector
                ->set($action, $this->actionConfig($action)), new Obj());

        $this->template->set('actions', $actions);

        return $this;
    }

    private function actionConfig($action): array
    {
        $route = $this->template->has('routes')
            && $this->template->get('routes')->has($action)
            ? $this->template->get('routes')->get($action)
            : $this->template->get('routePrefix').'.'.$action;

        [$routeOrPath, $value] = in_array($action, ['create', 'show', 'back'])
            ? ['route', $route]
            : ['path', route($route, $this->template->get('routeParams'), false)];

        return [
            'button' => config('enso.forms.buttons.'.$action),
            'forbidden' => $this->isForbidden($route),
            $routeOrPath => $value,
        ];
    }

    private function appendParams(): self
    {
        if (! $this->template->has('authorize')) {
            $this->template->set('authorize', config('enso.forms.authorize'));
        }

        if (! $this->template->has('dividerTitlePlacement')) {
            $placement = config('enso.forms.dividerTitlePlacement');
            $this->template->set('dividerTitlePlacement', $placement);
        }

        if (! $this->template->has('labels')) {
            $this->template->set('labels', config('enso.forms.labels'));
        }

        if (! $this->template->has('clearErrorsControl')) {
            $this->template->set('clearErrorsControl', true);
        }

        return $this;
    }

    private function isForbidden($route): bool
    {
        return $route !== 'back'
            && $this->template->get('authorize')
            && Auth::user()->cannot('access-route', $route);
    }
}
