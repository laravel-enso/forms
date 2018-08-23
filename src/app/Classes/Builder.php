<?php

namespace LaravelEnso\FormBuilder\app\Classes;

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
            ->computeActions();

        unset($this->template->routes, $this->template->routePrefix, $this->template->authorize);
    }

    private function setValues()
    {
        if (!$this->model) {
            return $this;
        }

        collect($this->template->sections)->each(function ($section) {
            collect($section->fields)->each(function ($field) {
                if (!$this->dirty->contains($field->name)) {
                    $field->value = $this->model->{$field->name};
                }
            });
        });

        return $this;
    }

    private function computeActions()
    {
        $this->template->actions = collect($this->template->actions)
            ->reduce(function ($collector, $action) {
                $actionConfig = [];
                $actionConfig['button'] = config('enso.forms.buttons.'.$action);
                $route = $this->routes[$action] ?? $this->template->routePrefix.'.'.$action;
                $actionConfig['forbidden'] = $this->isForbidden($route);

                [$routeOrPath, $value] = collect(['create', 'show'])->contains($action)
                    ? ['route', $route]
                    : ['path', route($route, is_null($this->model) ? [] : [$this->model->getKey()], false)];

                $actionConfig[$routeOrPath] = $value;

                if ($action === 'show') {
                    $actionConfig['id'] = $this->model->getKey();
                }

                $collector[$action] = $actionConfig;

                return $collector;
            }, []);
    }

    private function appendConfigParams()
    {
        if (!property_exists($this->template, 'authorize')) {
            $this->template->authorize = config('enso.forms.authorize');
        }

        if (!property_exists($this->template, 'dividerTitlePlacement')) {
            $this->template->dividerTitlePlacement = config('enso.forms.dividerTitlePlacement');
        }

        return $this;
    }

    private function isForbidden($route)
    {
        return $this->template->authorize
            && request()->user()
                ->cannot('access-route', $route);
    }
}
