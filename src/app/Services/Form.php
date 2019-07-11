<?php

namespace LaravelEnso\Forms\app\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Forms\app\Attributes\Actions;
use LaravelEnso\Helpers\app\Classes\JsonParser;
use LaravelEnso\Forms\app\Exceptions\TemplateException;

class Form
{
    private $model;
    private $template;
    private $dirty;

    public function __construct(string $filename)
    {
        $this->readTemplate($filename);
        $this->template->set('routeParams', new Obj);
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
        $this->template->set('actions', new Obj($actions));

        return $this;
    }

    public function routePrefix(string $prefix)
    {
        $this->template->set('routePrefix', $prefix);

        return $this;
    }

    public function title(string $title)
    {
        $this->template->set('title', $title);

        return $this;
    }

    public function icon(string $icon)
    {
        $this->template->set('icon', $icon);

        return $this;
    }

    public function route(string $action, string $route)
    {
        if (! $this->template->has('routes')) {
            $this->template->set('routes', new Obj());
        }

        $this->template->get('routes')->set($action, $route);

        return $this;
    }

    public function options(string $name, $value)
    {
        $this->field($name)->get('meta')->set('options', $value);

        return $this;
    }

    public function value(string $field, $value)
    {
        $this->field($field)->set('value', $value);
        $this->dirty->push($field);

        return $this;
    }

    public function hide($fields)
    {
        collect($fields)->each(function ($field) {
            $this->field($field)->get('meta')->set('hidden', true);
        });

        return $this;
    }

    public function hideSection($fields)
    {
        collect($fields)->each(function($field) {
            $this->section($field)->get('fields')
                ->each(function ($field) {
                    $field->get('meta')->set('hidden', true);
                });
        });

        return $this;
    }

    public function show($fields)
    {
        collect($fields)->each(function ($field) {
            $this->field($field)->get('meta')->set('hidden', false);
        });

        return $this;
    }

    public function disable($fields)
    {
        collect($fields)->each(function ($field) {
            $this->field($field)->get('meta')->set('disabled', true);
        });

        return $this;
    }

    public function readonly($fields)
    {
        collect($fields)->each(function ($field) {
            $this->field($field)->get('meta')->set('readonly', true);
        });

        return $this;
    }

    public function meta(string $field, string $param, $value)
    {
        $this->field($field)->get('meta')->set($param, $value);

        return $this;
    }

    public function append($prop, $value)
    {
        if (! $this->template->has('params')) {
            $this->template->set('params', new Obj());
        }

        $this->template->get('params')->set($prop, $value);

        return $this;
    }

    public function routeParams(array $params)
    {
        $this->template->set('routeParams', $params);

        return $this;
    }

    public function authorize(bool $authorize)
    {
        $this->template->set('authorize', $authorize);

        return $this;
    }

    public function labels(bool $labels)
    {
        $this->template->set('labels', $labels);

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
        $this->template = new Obj(
            (new JsonParser($filename))->array()
        );
    }

    private function method(string $method)
    {
        $this->template->set('method', $method);

        if (! $this->template->has('actions')) {
            $this->template->set('actions', $this->defaultActions());

            return $this;
        }

        return $this;
    }

    private function defaultActions()
    {
        $actions = $this->template->get('method') === 'post'
            ? Actions::Create
            : Actions::Update;

        return (new Obj($actions))
            ->filter(function ($action) {
                return Route::has($this->template->get('routePrefix').'.'.$action)
                    || $action === 'back';
            });
    }

    private function section($field)
    {
        $section = $this->template->get('sections')
            ->first(function ($section) use ($field) {
                return $section->get('fields')
                    ->contains(function ($sectionField) use ($field) {
                        return $sectionField->get('name') === $field;
                    });
            });

        if (! $section) {
            $this->throwMissingFieldException($field);
        }
        
        return $section;
    }

    private function field(string $fieldName)
    {
        $field = $this->template->get('sections')
            ->reduce(function ($fields, $section) {
                return $fields->merge($section->get('fields'));
            }, collect())->first(function ($field) use ($fieldName) {
                return $field->get('name') === $fieldName;
            });

        if (! $field) {
            $this->throwMissingFieldException($fieldName);
        }

        return $field;
    }

    private function needsValidation()
    {
        return ! app()->environment('production')
            || config('enso.forms.validations') === 'always';
    }

    private function throwMissingFieldException($fieldName)
    {
        throw new TemplateException(__(
            'The :field field is missing from the JSON template',
            ['field' => $fieldName]
        ));
    }
}
