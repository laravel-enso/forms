# Forms

[![License](https://poser.pugx.org/laravel-enso/forms/license)](LICENSE)
[![Stable](https://poser.pugx.org/laravel-enso/forms/version)](https://packagist.org/packages/laravel-enso/forms)
[![Downloads](https://poser.pugx.org/laravel-enso/forms/downloads)](https://packagist.org/packages/laravel-enso/forms)
[![PHP](https://img.shields.io/badge/php-8.0%2B-777bb4.svg)](composer.json)
[![Issues](https://img.shields.io/github/issues/laravel-enso/forms.svg)](https://github.com/laravel-enso/forms/issues)
[![Merge Requests](https://img.shields.io/github/issues-pr/laravel-enso/forms.svg)](https://github.com/laravel-enso/forms/pulls)

## Description

Forms is the JSON-based form builder used across Laravel Enso backends.

The package loads form templates from JSON, builds create and edit payloads around Eloquent models, computes actions and meta configuration, validates template structure, and ships test traits for common create/edit/destroy flows.

It is a backend payload builder, not a standalone UI package. The canonical frontend companion is [`@enso-ui/forms`](https://github.com/enso-ui/forms), which renders the payloads produced by this package.

## Installation

Install the package:

```bash
composer require laravel-enso/forms
```

Optional publishes:

```bash
php artisan vendor:publish --tag=forms-config
php artisan vendor:publish --tag=forms-resources
```

## Features

- JSON template loading through `Form`.
- Create and edit payload generation for Eloquent-backed forms.
- Runtime mutation helpers for values, labels, options, visibility, readonly state, and route metadata.
- Template validation for structure, actions, routes, and fields.
- PHPUnit test traits for form workflows.
- Direct integration with the Enso frontend form renderer and its field components.

## Usage

Create or edit a form payload:

```php
use LaravelEnso\Forms\Services\Form;

$form = new Form(__DIR__.'/Templates/example.json');

$createPayload = $form->create();
$editPayload = $form->edit($model);
```

### Template structure

The JSON template must include at least:

- `method`
- `sections`
- `routeParams`

The root template may also include:

- `title`, `icon`
- `actions`
- `authorize`
- `autosave`
- `clearErrorsControl`
- `debounce`
- `dividerTitlePlacement`
- `labels`
- `params`
- `routePrefix`
- `routes`
- `tabs`

Each section must define:

- `columns`
- `fields`

Optional section attributes are:

- `divider`
- `title`
- `column`
- `tab`
- `slot`
- `hidden`

Each field must define:

- `label`
- `name`
- `value`
- `meta`

### Minimal template example

```json
{
    "title": "Example",
    "icon": "user",
    "method": null,
    "routePrefix": "administration.users",
    "routeParams": {},
    "actions": ["store", "update", "destroy", "back"],
    "sections": [
        {
            "columns": 2,
            "title": "General",
            "fields": [
                {
                    "label": "Name",
                    "name": "name",
                    "value": "",
                    "meta": {
                        "type": "input",
                        "content": "text",
                        "custom": false
                    }
                },
                {
                    "label": "Roles",
                    "name": "roles",
                    "value": [],
                    "meta": {
                        "type": "select",
                        "multiple": true,
                        "source": "administration.roles.options",
                        "custom": false
                    }
                }
            ]
        }
    ]
}
```

### `Form` service API

The `LaravelEnso\Forms\Services\Form` object is not just a loader. It is a fluent mutator for the template before `create()` or `edit()` finalizes the payload.

Common methods:

- `create(?Model $model = null)`
- `edit(Model $model)`
- `actions(...$actions)`
- `routePrefix(string $prefix)`
- `route(string $action, string $route)`
- `routeParams(array $params)`
- `title(string $title)`
- `icon(string $icon)`
- `append(string $param, mixed $value)`
- `authorize(bool $authorize)`
- `labels(bool $labels)`

Field and section mutators:

- `label(string $field, string $value)`
- `options(string $field, mixed $value)`
- `value(string $field, mixed $value)`
- `meta(string $field, string $param, mixed $value)`
- `columns(string $section, int $value)`
- `hide(...$fields)` / `show(...$fields)`
- `disable(...$fields)`
- `readonly(...$fields)`
- `hideSection(...$sections)` / `showSection(...$sections)`
- `hideTab(...$tabs)` / `showTab(...$tabs)`

Example:

```php
use LaravelEnso\Forms\Services\Form;

$form = (new Form(app_path('Forms/Templates/users.json')))
    ->title('Edit User')
    ->icon('user')
    ->routePrefix('administration.users')
    ->append('locale', app()->getLocale())
    ->label('email', 'Login Email')
    ->options('role_id', $roles)
    ->value('is_active', true)
    ->readonly('email')
    ->hide('password')
    ->meta('phone', 'placeholder', '+40 700 000 000');

$payload = $form->edit($user);
```

### Supported field types

The backend validator accepts these `meta.type` values:

- `input`
- `select`
- `datepicker`
- `timepicker`
- `textarea`
- `password`
- `wysiwyg`

The frontend companion `@enso-ui/forms` currently renders these combinations:

- `input` with `content: text`
- `input` with `content: number`
- `input` with `content: email`
- `input` with `content: password`
- `input` with `content: encrypt`
- `input` with `content: money`
- `input` with `content: checkbox`
- `select`
- `textarea`
- `datepicker`
- `timepicker`
- `wysiwyg`

Important behavior:

- encrypted inputs are returned as `************************` when the model already has a value
- multi-select values are normalized to arrays of tracked keys
- datepicker values are formatted with the configured or field-specific format
- select `source` routes are converted to relative application paths
- `wysiwyg` fields receive the configured TinyMCE API key automatically

### Supported meta keys

The validator accepts the following optional `meta` keys:

- `options`, `multiple`, `custom`, `content`
- `step`, `min`, `max`
- `disabled`, `readonly`, `hidden`
- `source`, `format`, `altFormat`, `time`
- `rows`, `placeholder`
- `trackBy`, `label`
- `tooltip`, `symbol`, `precision`, `thousand`, `decimal`
- `positive`, `negative`, `zero`
- `resize`, `translated`
- `time12hr`, `disable-clear`
- `objects`, `toolbar`, `plugins`, `taggable`
- `searchMode`, `params`, `pivotParams`, `customParams`

### Template validation rules

`Validator` runs four checks:

- `Structure`: verifies root attributes, section format, columns, and tab consistency
- `Actions`: only allows Enso form actions supported by the package
- `Routes`: requires actual named routes for every action except `back`
- `Fields`: verifies field attributes, checkbox values, select value shape, and delegates `meta` validation

Notable enforced rules:

- create forms can use only `back` and `store`
- update forms can use only `back`, `create`, `show`, `update`, and `destroy`
- `select` fields must define `options` or `source`
- checkbox fields must have boolean values
- multi-select fields must receive array or object values
- `columns: custom` requires an integer `column` per field

### Frontend companion

The canonical renderer is [`@enso-ui/forms`](https://github.com/enso-ui/forms).

Its public Vue components are:

- `EnsoForm`
- `VueForm`
- `CoreForm`

The package integrates with companion UI packages such as:

- `@enso-ui/select`
- `@enso-ui/datepicker`
- `@enso-ui/wysiwyg`
- `@enso-ui/money`
- `@enso-ui/switch`
- `@enso-ui/tabs`

`EnsoForm` also exposes runtime helpers to the host application, including:

- `fetch()`
- `submit()`
- `field(name)`
- `param(name)`
- `routeParam(name)`
- `fill(state)`
- `setOriginal()`
- `undo()`
- `hideField(name)` / `showField(name)`
- `hideTab(name)` / `showTab(name)`

## API

### Services

- `LaravelEnso\\Forms\\Services\\Form`
- `LaravelEnso\\Forms\\Services\\Builder`
- `LaravelEnso\\Forms\\Services\\Validator`

### Published resources

- `config/enso/forms.php`
- `app/Forms/Builders/ModelForm.php`
- `app/Forms/Templates/template.php`

### Configuration highlights

- `validations`
- `buttons`
- `altDateFormat`
- `selectPlaceholder`
- `authorize`
- `dividerTitlePlacement`
- `labels`
- `tinyMCEApiKey`

### Test traits

- `CreateForm`
- `EditForm`
- `DestroyForm`

## Depends On

Required Enso packages:

- [`laravel-enso/enums`](https://docs.laravel-enso.com/backend/enums.html) [↗](https://github.com/laravel-enso/enums)
- [`laravel-enso/helpers`](https://docs.laravel-enso.com/backend/helpers.html) [↗](https://github.com/laravel-enso/helpers)

Companion frontend package:

- [`@enso-ui/forms`](https://docs.laravel-enso.com/frontend/forms.html) [↗](https://github.com/enso-ui/forms)

## Contributions

are welcome. Pull requests are great, but issues are good too.

Thank you to all the people who already contributed to Enso!
