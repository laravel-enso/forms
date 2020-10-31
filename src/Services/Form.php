<?php

namespace LaravelEnso\Forms\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use LaravelEnso\Forms\Attributes\Actions;
use LaravelEnso\Forms\Exceptions\Template;
use LaravelEnso\Helpers\Services\JsonReader;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Helpers\Traits\When;

class Form
{
    use When;

    private ?Model $model;
    private Obj $template;
    private Collection $dirty;

    public function __construct(string $filename)
    {
        $this->template = new Obj((new JsonReader($filename))->array());
        $this->dirty = new Collection();
    }

    public function create(?Model $model = null): Obj
    {
        $this->model = $model;

        if (! $this->template->has('routeParams')) {
            $this->routeParams([]);
        }

        $this->method('post')->build();

        return $this->template;
    }

    public function edit(Model $model): Obj
    {
        $this->model = $model;

        if (! $this->template->has('routeParams')) {
            $param = Str::camel(class_basename($model));
            $this->routeParams([$param => $model->getKey()]);
        }

        $this->method('patch')->build();

        return $this->template;
    }

    public function actions($actions): self
    {
        $this->template->set('actions', new Obj($actions));

        return $this;
    }

    public function routePrefix(string $prefix): self
    {
        $this->template->set('routePrefix', $prefix);

        return $this;
    }

    public function title(string $title): self
    {
        $this->template->set('title', $title);

        return $this;
    }

    public function icon(string $icon): self
    {
        $this->template->set('icon', $icon);

        return $this;
    }

    public function route(string $action, string $route): self
    {
        if (! $this->template->has('routes')) {
            $this->template->set('routes', new Obj());
        }

        $this->template->get('routes')->set($action, $route);

        return $this;
    }

    public function label(string $field, string $value): self
    {
        $this->field($field)->set('label', $value);

        return $this;
    }

    public function options(string $field, $value): self
    {
        $this->field($field)->get('meta')->set('options', $value);

        return $this;
    }

    public function value(string $field, $value): self
    {
        $this->field($field)->set('value', $value);
        $this->dirty->push($field);

        return $this;
    }

    public function columns(string $field, int $value): self
    {
        $this->section($field)->set('columns', $value);

        return $this;
    }

    public function hide($fields): self
    {
        (new Collection($fields))->each(fn ($field) => $this->field($field)
            ->get('meta')->set('hidden', true));

        return $this;
    }

    public function hideSection($fields): self
    {
        $this->sectionVisibility($fields, $hidden = true);

        return $this;
    }

    public function showSection($fields): self
    {
        $this->sectionVisibility($fields, $hidden = false);

        return $this;
    }

    public function hideTab($tabs): self
    {
        $this->tabVisibility($tabs, $hidden = true);

        return $this;
    }

    public function showTab($tabs): self
    {
        $this->tabVisibility($tabs, $hidden = false);

        return $this;
    }

    public function show($fields): self
    {
        (new Collection($fields))->each(fn ($field) => $this->field($field)
            ->get('meta')->set('hidden', false));

        return $this;
    }

    public function disable($fields): self
    {
        (new Collection($fields))->each(fn ($field) => $this->field($field)
            ->get('meta')->set('disabled', true));

        return $this;
    }

    public function readonly($fields): self
    {
        (new Collection($fields))->each(fn ($field) => $this->field($field)
            ->get('meta')->set('readonly', true));

        return $this;
    }

    public function meta(string $field, string $param, $value): self
    {
        $this->field($field)->get('meta')->set($param, $value);

        return $this;
    }

    public function append(string $param, $value): self
    {
        if (! $this->template->has('params')) {
            $this->template->set('params', new Obj());
        }

        $this->template->get('params')->set($param, $value);

        return $this;
    }

    public function routeParams(array $params): self
    {
        $this->template->set('routeParams', $params);

        return $this;
    }

    public function authorize(bool $authorize): self
    {
        $this->template->set('authorize', $authorize);

        return $this;
    }

    public function labels(bool $labels): self
    {
        $this->template->set('labels', $labels);

        return $this;
    }

    public function sectionVisibility($fields, bool $hidden): self
    {
        (new Collection($fields))
            ->each(fn ($field) => $this->section($field)->get('fields')
                ->each(fn ($field) => $field->get('meta')->set('hidden', $hidden)));

        return $this;
    }

    public function tabVisibility($tabs, bool $hidden): self
    {
        $tabs = (new Collection($tabs));

        $this->template->get('sections')->each(fn ($section) => $tabs->when(
            $tabs->contains($section->get('tab')),
            fn () => $section->get('fields')
                ->each(fn ($field) => $field->get('meta')->set('hidden', $hidden))
        ));

        return $this;
    }

    private function build(): void
    {
        if ($this->needsValidation()) {
            (new Validator($this->template))->run();
        }

        (new Builder($this->template, $this->dirty, $this->model))->run();
    }

    private function method(string $method): self
    {
        $this->template->set('method', $method);

        if (! $this->template->has('actions')) {
            $this->template->set('actions', $this->defaultActions());
        }

        return $this;
    }

    private function defaultActions(): Obj
    {
        $actions = $this->template->get('method') === 'post'
            ? Actions::Create
            : Actions::Update;

        return (new Obj($actions))->filter(fn ($action) => Route::has(
            "{$this->template->get('routePrefix')}.{$action}"
        ) || $action === 'back');
    }

    private function section(string $field): Obj
    {
        $section = $this->template->get('sections')
            ->first(fn ($section) => $section->get('fields')
                ->contains(fn ($sectionField) => $sectionField->get('name') === $field));

        if (! $section) {
            $this->throwMissingFieldException($field);
        }

        return $section;
    }

    private function field(string $fieldName): Obj
    {
        $field = $this->template->get('sections')
            ->reduce(fn ($fields, $section) => $fields
                ->merge($section->get('fields')), new Collection())
            ->first(fn ($field) => $field->get('name') === $fieldName);

        if (! $field) {
            $this->throwMissingFieldException($fieldName);
        }

        return $field;
    }

    private function needsValidation(): bool
    {
        return (new Collection([App::environment(), 'always']))->contains(
            Config::get('enso.forms.validations')
        );
    }

    private function throwMissingFieldException($fieldName): void
    {
        throw Template::fieldMissing($fieldName);
    }
}
