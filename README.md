<!--h-->
# FormBuilder

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/a3cf79a9ca584f08b3be0246cb488788)](https://www.codacy.com/app/laravel-enso/FormBuilder?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=laravel-enso/FormBuilder&amp;utm_campaign=Badge_Grade)
[![StyleCI](https://styleci.io/repos/99695155/shield?branch=master)](https://styleci.io/repos/99695155)
[![License](https://poser.pugx.org/laravel-enso/formbuilder/license)](https://packagist.org/packages/laravel-enso/formbuilder)
[![Total Downloads](https://poser.pugx.org/laravel-enso/formbuilder/downloads)](https://packagist.org/packages/laravel-enso/formbuilder)
[![Latest Stable Version](https://poser.pugx.org/laravel-enso/formbuilder/version)](https://packagist.org/packages/laravel-enso/formbuilder)
<!--/h-->

JSON-based Form builder for [Laravel Enso](https://github.com/laravel-enso/Enso)

[![Watch the demo](https://laravel-enso.github.io/formbuilder/screenshots/bulma_109_thumb.png)](https://laravel-enso.github.io/formbuilder/videos/bulma_demo_01.webm)

<sup>click on the photo to view a short demo in compatible browsers</sup>

### Features
- allows for quick creation of forms
- uses a JSON template file for generating the form
- uses it's own VueJS components, such as `vue-select` and `datepicker` for an improved experience
- for most forms, the json template is all that it's needed
- provides helpful error messages when the template is missing parameters or unexpected values ar found
- when needed, allows the customization of form components in order to cover all scenarios
- comes with a `template.json` file that can be used as an example when starting out
- integrates with the Laravel Request Validation for seamless usage and reusability
- uses the enso toast notifications for stylish feedback on the various actions

### Under the Hood
- a template file is needed in order to generate the form data structure object
- the `Form` object has to be used in the back-end (controller) to parse the template, get additional parameters if needed, and build the structure
- although in most common scenarios you can give all the required configuration in the template file, 
the `Form` class has fluent helper functions for setting/overriding most attributes
- a `VueForm` object needs to be included in the view/page/parent component, taking the form-builder's resulting object as parameter 

### Installation Steps

No extra installation steps are required, as this package is already included in the base install of [Laravel Enso](https://github.com/laravel-enso/Enso).

### Usage

1. Create a template file for the new form, using `template.json` as an example, and place it inside `app/Forms` (recommended)
2. Create and setup in your controller method the `Form` object, and return the resulting data

    ````php
    $form = (new Form(app_path('Forms/owner.json')))
                ->title('Create a new Owner')
                ->options('role_list', Role::pluck('name', 'id'))
                ->create(); 
                
    return compact('form');
    ````
    
    ````php
    $owner = Owner::find($ownerId);
    $form = (new Form(app_path('Forms/owner.json')))
                ->title('Owner Edit')
                ->options('role_list', Role::pluck('name', 'id'))
                ->edit($owner); 
                
    return compact('form');
    ````  

6. Add inside your page/component

    ````
    <vue-form class="box"
        :data="form">
    </vue-form>
    ````

### VueJS Components
The VueForm.vue components takes the following parameters:
- `data`, object, represents the configuration used to render the form and its elements | required
- `params`, object, can be used to send additionnal parameters with the form request | default `null` | (optional)

Note: when sending extra parameters, on the back-end they can be found in the request's `_params` attribute.  

Note: when creating a resource and no redirect is given in the POST response, the form does not perform a redirect.

### Advanced usage
The `Form` class provided the following fluent helper functions:
- `actions(array $actions)`, sets the actions available on the form. 
Valid actions are `create`, `store`, `update` and `delete` 
- `routePrefix(string $prefix)`, sets the route prefix that then is used with the various action default endpoints,
- `title(string $title)`, the title for the form,
- `icon(string $icon)`, the icon shown alongside the title
- `route(string $action, string $route)`, permits setting a specific route for a given action
- `options(string $name, $value)`, sets the available option list for a given select attribute
Commonly used to override the form value.
- `value(string $field, $value)`, sets the starting value for form element
Commonly used to override the form value.
- `meta(string $field, string $param, $value)`, sets a specific value, for a meta param, for the given field
- `authorize(bool $authorize)`, set the authorize flag for the form.
If this value is not given in the form, the global default value is taken from the config file 

### Publishes

- `php artisan vendor:publish --tag=form-assets` - the VueJS components,
- `php artisan vendor:publish --tag=form-config` - the configuration file,
- `php artisan vendor:publish --tag=forms` - the example JSON template file,
- `php artisan vendor:publish --tag=enso-assets` - a common alias for when wanting to update the VueJS components,
once a newer version is released, usually used with the `--force` flag

### Notes

The [Laravel Enso](https://github.com/laravel-enso/Enso) package comes with this package included.

Depends on:
- [Helpers](https://github.com/laravel-enso/VueComponents) for utility objects


<!--h-->
### Contributions

are welcome. Pull requests are great, but issues are good too.

### License

This package is released under the MIT license.
<!--/h-->