<?php

namespace LaravelEnso\FormBuilder\app\Classes;

use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Helpers\app\Classes\JsonParser;
use LaravelEnso\FormBuilder\app\Classes\Attributes\Actions;
use LaravelEnso\FormBuilder\app\Exceptions\TemplateException;

class Form
{
    private const CreateActions = ['back', 'store'];
    private const UpdateActions = ['back', 'create', 'show', 'update', 'destroy'];

    private $model;
    private $template;
    private $dirty;

    public function __construct(string $filename)
    {
        $this->readTemplate($filename);
        $this->template->routeParams = [];

        $this->dirty = collect();
    }

    public function create(Model $model = null)
    {
        $this->model = $model;

        $this->method('post')
            ->build();

        return $this->template;
    }

    public function edit(Model $model)
    {
        $this->model = $model;

        $this->method('patch')
            ->routeParams([
                camel_case(class_basename($model)) => $model->getKey(),
            ])->build();

        return $this->template;
    }

    public function actions($actions)
    {
        $this->template->actions = (array) $actions;

        return $this;
    }

    public function routePrefix(string $prefix)
    {
        $this->template->routePrefix = $prefix;

        return $this;
    }

    public function title(string $title)
    {
        $this->template->title = $title;

        return $this;
    }

    public function icon(string $icon)
    {
        $this->template->icon = $icon;

        return $this;
    }

    public function route(string $action, string $route)
    {
        $this->template->routes[$action] = $route;

        return $this;
    }

    public function options(string $name, $value)
    {
        $this->field($name)->meta->options = $value;

        return $this;
    }

    public function value(string $field, $value)
    {
        $this->field($field)->value = $value;
        $this->dirty->push($field);

        return $this;
    }

    public function hide($fields)
    {
        collect($fields)->each(function ($field) {
            $this->field($field)->meta->hidden = true;
        });

        return $this;
    }

    public function show($fields)
    {
        collect($fields)->each(function ($field) {
            $this->field($field)->meta->hidden = false;
        });

        return $this;
    }

    public function disable($fields)
    {
        collect($fields)->each(function ($field) {
            $this->field($field)->meta->disabled = true;
        });

        return $this;
    }

    public function readonly($fields)
    {
        collect($fields)->each(function ($field) {
            $this->field($field)->meta->readonly = true;
        });

        return $this;
    }

    public function meta(string $field, string $param, $value)
    {
        $this->field($field)->meta->{$param} = $value;

        return $this;
    }

    public function append($prop, $value)
    {
        if (! property_exists($this->template, 'params')) {
            $this->template->params = new \stdClass();
        }

        $this->template->params->$prop = $value;

        return $this;
    }

    public function routeParams($params)
    {
        collect($params)->each(function ($value, $key) {
            $this->template->routeParams[$key] = $value;
        });

        return $this;
    }

    public function authorize(bool $authorize)
    {
        $this->template->authorize = $authorize;

        return $this;
    }

    private function build()
    {
        if ($this->needsValidation()) {
            (new Validator($this->template))->run();
        }

        (new Builder($this->template, $this->dirty, $this->model))->run();
    }

    private function readTemplate(string $filename)
    {
        $this->template = (new JsonParser($filename))->object();
    }

    private function method(string $method)
    {
        $this->template->method = $method;

        if (! isset($this->template->actions)) {
            $this->template->actions = $this->defaultActions();

            return $this;
        }

        return $this;
    }

    private function defaultActions()
    {
        $actions = $this->template->method === 'post'
            ? self::CreateActions
            : self::UpdateActions;

        return collect($actions)
            ->filter(function ($action) {
                return \Route::has($this->template->routePrefix.'.'.$action)
                    || $action === 'back';
            })->toArray();
    }

    private function field(string $name)
    {
        $field = collect($this->template->sections)
            ->reduce(function ($fields, $section) {
                return $fields->merge($section->fields);
            }, collect())->first(function ($field) use ($name) {
                return $field->name === $name;
            });

        if (! $field) {
            throw new TemplateException(__(
                'The :field field is missing from the JSON template',
                ['field' => $name]
            ));
        }

        return $field;
    }

    private function needsValidation()
    {
        return ! app()->environment('production')
            || config('enso.datatable.validations') === 'always';
    }
}
