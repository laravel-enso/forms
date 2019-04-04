<?php

namespace LaravelEnso\FormBuilder\app\Classes;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Helpers\app\Classes\Obj;

class Builder
{
    private $template;
    private $model;
    private $dirty;

    public function __construct(Obj $template, Collection $dirty, Model $model = null)
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

        $this->template->forget(['routes', 'routePrefix', 'authorize']);
    }

    private function setValues()
    {
        if (! $this->model) {
            return $this;
        }

        collect($this->template->get('sections'))
            ->each(function ($section) {
                collect($section->get('fields'))
                    ->each(function ($field) {
                        $field->set('value', $this->value($field));
                    });
            });

        return $this;
    }

    private function value($field)
    {
        $meta = $field->get('meta');

        $value = $this->dirty->contains($field->get('name'))
            ? $field->get('value')
            : $this->model->{$field->get('name')};

        if ($meta->get('type') === 'datepicker'
            && is_object($this->model->{$field->name})
            && $value instanceof Carbon) {
            return $value
                ->format($this->dateFormat($field));
        }

        if ($meta->get('type') === 'select' && $meta->get('multiple')) {
            if ($value instanceof Collection) {
                $trackBy = $meta->get('trackBy') ?? 'id';

                return $value->pluck($trackBy);
            }
        }

        return $value;
    }

    private function computeActions()
    {
        $actions = collect($this->template->get('actions'))
            ->reduce(function ($collector, $action) {
                $collector[$action] = $this->actionConfig($action);

                return $collector;
            }, []);

        $this->template->set('actions', $actions);

        return $this;
    }

    private function actionConfig($action)
    {
        $route = $this->template->has('routes')
            && $this->template->get('routes')->has($action)
            ? $this->template->get('routes')->get($action)
            : $this->template->get('routePrefix').'.'.$action;

        [$routeOrPath, $value] = collect(['create', 'show', 'back'])->contains($action)
            ? ['route', $route]
            : ['path', route($route, $this->template->get('routeParams'), false)];

        return [
            'button' => config('enso.forms.buttons.'.$action),
            'forbidden' => $this->isForbidden($route),
            $routeOrPath => $value,
        ];
    }

    private function computeSelects()
    {
        collect($this->template->get('sections'))
            ->each(function ($section) {
                collect($section->get('fields'))
                    ->each(function ($field) {
                        $meta = $field->get('meta');

                        if ($meta->get('type') === 'select') {
                            if ($meta->has('options') && is_string($meta->get('options'))) {
                                $enum = $meta->get('options');
                                $meta->set('options', $enum::select());
                            }

                            if (! $meta->has('placeholder')) {
                                $meta->set('placeholder', config('enso.forms.selectPlaceholder'));
                            }

                            if (! $meta->has('trackBy')) {
                                $meta->set('trackBy', 'id'); //TODO refactor to config
                            }

                            if (! $meta->has('label')) {
                                $meta->set('label', 'name'); //TODO refactor to config
                            }

                            if ($meta->has('source')) {
                                $meta->set('source', route($meta->get('source')));
                            }
                        }
                    });
            });
    }

    private function appendConfigParams()
    {
        if (! $this->template->has('authorize')) {
            $this->template->set('authorize', config('enso.forms.authorize'));
        }

        if (! $this->template->has('dividerTitlePlacement')) {
            $this->template->set(
                'dividerTitlePlacement', config('enso.forms.dividerTitlePlacement')
            );
        }

        if (! $this->template->has('labels')) {
            $this->template->set('labels', config('enso.forms.labels'));
        }

        return $this;
    }

    private function dateFormat($field)
    {
        $meta = $field->get('meta');

        if (! $meta->has('format')) {
            $meta->set('format', config('enso.forms.dateFormat'));
        }

        return $meta->get('format');
    }

    private function isForbidden($route)
    {
        return $route !== 'back'
            && $this->template->get('authorize')
            && request()->user()
                ->cannot('access-route', $route);
    }
}
