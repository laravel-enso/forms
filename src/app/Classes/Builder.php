<?php

namespace LaravelEnso\FormBuilder\app\Classes;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class Builder
{
    private $template;
    private $model;
    private $dirty;

    public function __construct($template, Collection $dirty, Model $model = null)
    {
        $this->template = $template;
        $this->model = $model;
        $this->dirty = $dirty;
    }

    public function run()
    {
        $this->appendConfigParams()
            ->setValues()
            ->computeActions()
            ->computeSelects();

        unset(
            $this->template->routes,
            $this->template->routePrefix,
            $this->template->authorize
        );
    }

    private function setValues()
    {
        if (! $this->model) {
            return $this;
        }

        collect($this->template->sections)->each(function ($section) {
            collect($section->fields)->each(function ($field) {
                $field->value = $this->value($field);
            });
        });

        return $this;
    }

    private function value($field)
    {
        $value = $this->dirty->contains($field->name)
            ? $field->value
            : $this->model->{$field->name};

        if ($field->meta->type === 'datepicker'
            && is_object($this->model->{$field->name})
            && $value instanceof Carbon) {
            return $value
                ->format($this->dateFormat($field));
        }

        if ($field->meta->type === 'select'
            && isset($field->meta->multiple)
            && $field->meta->multiple) {
            if ($value instanceof Collection) {
                $trackBy = $field->meta->trackBy ?? 'id';

                return $value->pluck($trackBy);
            }
        }

        return $value;
    }

    private function computeActions()
    {
        $this->template->actions = collect($this->template->actions)
            ->reduce(function ($collector, $action) {
                $collector[$action] = $this->actionConfig($action);

                return $collector;
            }, []);

        return $this;
    }

    private function actionConfig($action)
    {
        $route = $this->template->routes[$action]
            ?? $this->template->routePrefix.'.'.$action;

        [$routeOrPath, $value] = collect(['create', 'show', 'back'])->contains($action)
            ? ['route', $route]
            : ['path', route($route, $this->template->routeParams, false)];

        return [
            'button' => config('enso.forms.buttons.'.$action),
            'forbidden' => $this->isForbidden($route),
            $routeOrPath => $value,
        ];
    }

    private function computeSelects()
    {
        collect($this->template->sections)->each(function ($section) {
            collect($section->fields)->each(function ($field) {
                if ($field->meta->type === 'select') {
                    if (property_exists($field->meta, 'options')
                        && is_string($field->meta->options)) {
                        $field->meta->options = $field->meta->options::select();
                    }

                    if (! property_exists($field->meta, 'placeholder')) {
                        $field->meta->placeholder = config('enso.forms.selectPlaceholder');
                    }

                    if (! property_exists($field->meta, 'trackBy')) {
                        $field->meta->trackBy = 'id'; //TODO refactor to config
                    }

                    if (! property_exists($field->meta, 'label')) {
                        $field->meta->label = 'name'; //TODO refactor to config
                    }

                    if (property_exists($field->meta, 'source')) {
                        $field->meta->source = route($field->meta->source);
                    }
                }
            });
        });
    }

    private function appendConfigParams()
    {
        if (! property_exists($this->template, 'authorize')) {
            $this->template->authorize = config('enso.forms.authorize');
        }

        if (! property_exists($this->template, 'dividerTitlePlacement')) {
            $this->template->dividerTitlePlacement = config('enso.forms.dividerTitlePlacement');
        }

        return $this;
    }

    private function dateFormat($field)
    {
        if (! property_exists($field->meta, 'format')) {
            $field->meta->format = config('enso.forms.dateFormat');
        }

        return $field->meta->format;
    }

    private function isForbidden($route)
    {
        return $route !== 'back'
            && $this->template->authorize
            && request()->user()
                ->cannot('access-route', $route);
    }
}
