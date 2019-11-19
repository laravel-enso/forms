<?php

namespace LaravelEnso\Forms\app\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use LaravelEnso\Forms\app\Attributes\Actions;
use LaravelEnso\Forms\app\Exceptions\TemplateException;
use LaravelEnso\Helpers\app\Classes\JsonParser;
use LaravelEnso\Helpers\app\Classes\Obj;

class Form
{
    private $model;
    private $template;
    private $dirty;

    public function __construct(string $filename)
    {
        $this->readTemplate($filename)
            ->routeParams([]);

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
                Str::camel(class_basename($model)) => $model->getKey(),
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
        $this->sectionVisibility($fields, $hidden = true);

        return $this;
    }

    public function showSection($fields)
    {
        $this->sectionVisibility($fields, $hidden = false);

        return $this;
    }

    public function hideTab($tabs)
    {
        $this->tabVisibility($tabs, $hidden = true);

        return $this;
    }

    public function showTab($tabs)
    {
        $this->tabVisibility($tabs, $hidden = false);

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

        return $this;
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

    public function sectionVisibility($fields, bool $hidden)
    {
        collect($fields)->each(function ($field) use ($hidden) {
            $this->section($field)->get('fields')
                ->each(function ($field) use ($hidden) {
                    $field->get('meta')->set('hidden', $hidden);
                });
        });

        return $this;
    }

    public function tabVisibility($tabs, $hidden)
    {
        $this->template->get('sections')
            ->each(function ($section) use ($tabs, $hidden) {
                if (collect($tabs)->contains($section->get('tab'))) {
                    $section->get('fields')->each(function ($field) use ($hidden) {
                        $field->get('meta')->set('hidden', $hidden);
                    });
                }
            });

        return $this;
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
        return collect([App::environment(), 'always'])->contains(
            config('enso.forms.validations')
        );
    }

    private function throwMissingFieldException($fieldName)
    {
        throw TemplateException::fieldMissing($fieldName);
    }
}
