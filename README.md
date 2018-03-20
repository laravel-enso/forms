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
- flexible form layout, that supports grouping inputs into logical sections and columns of different widths, 
even on the same row 
- uses it's own VueJS components, such as `vue-select` and `datepicker` for an improved experience
- `VueFormSs.vue` a server-side form wrapper is available that can be used to fetch the form configuration 
- for most forms, the json template is all that it's needed
- provides helpful error messages when the template is missing parameters or unexpected values are found
- when needed, allows the customization of form components in order to cover all scenarios
- comes with a `template.json` file that can be used as an example when starting out
- integrates with the Laravel Request Validation for seamless usage and reusability
- uses the Enso toast notifications for stylish feedback on the various actions

### Under the Hood
- a template file is needed in order to generate the form data structure object
- the `Form` object has to be used in the back-end (controller) to parse the template, get additional parameters if needed, and build the structure
- although in most common scenarios you can give all the required configuration in the template file, 
the `Form` class has fluent helper functions for setting/overriding most attributes
- a `VueForm` component needs to be included in the view/page/parent component, taking the form-builder's resulting object as parameter
- a `VueFormSs` component should be included in the view/page/parent component, taking the route params needed to 
make the ajax request and fetch the form configuration

### Installation Steps

No extra installation steps are required, as this package is already included in the base install of [Laravel Enso](https://github.com/laravel-enso/Enso).

### Usage
When using the form builder functionality, you will be needing several items:
- the JSON template that configures the form's layout, inputs, actions, etc.
- usually, an endpoint that reads the configuration and returns a properly formatted form configuration
- the `vue-from` VueJS components inside your page/app that renders the form based on the configuration
- one or more endpoints for your form's actions, such as storing, updating, deleting.

1. Create a template file for the new form, using `template.json` as an example, and place it inside `app/Forms` (recommended).
Below is an example of such a template:
    ```js
    {
        "title": "My Title",
        "icon": "location-arrow",
        "method": null,
        "routePrefix": "core.addresses",
        "sections": [
            {
                "columns": 3,
                "fields": [
                    {
                    "label": "County",
                    "name": "county_id",
                    "value": null,
                    "meta": {
                        "custom": true,
                        "type": "select",
                        "multiple": false,
                        "source": null,
                        "options": []
                    }
                }, 
                    {
                        "label": "Locality",
                        "name": "locality_id",
                        "value": null,
                        "meta": {
                            "custom": true,
                            "type": "select",
                            "multiple": false,
                            "source": "core.addresses.localitiesSelectOptions",
                            "options": [],
                            "label": "label"
                        }
                    }, 
                    {
                        "label": "Neighborhood",
                        "name": "neighborhood",
                        "value": null,
                        "meta": {
                            "custom": false,
                            "type": "input",
                            "content": "text",
                            "disabled": false
                        }
                    }
                ]
            }, 
            {
                "columns": "custom",
                "fields": [
                    {
                        "label": "Street Type",
                        "name": "street_type",
                        "value": null,
                        "column": 3,
                        "meta": {
                            "type": "select",
                            "multiple": false,
                            "source": null,
                            "options": []
                        }
                    }, 
                    {
                        "label": "Street",
                        "name": "street",
                        "value": null,
                        "column": 6,
                        "meta": {
                            "custom": false,
                            "type": "input",
                            "content": "text",
                            "disabled": false
                        }
                    }, 
                    {
                        "label": "Number",
                        "name": "number",
                        "value": null,
                        "column": 3,
                        "meta": {
                            "custom": false,
                            "type": "input",
                            "content": "text",
                            "disabled": false
                        }
                    }
                ]
            },
            {
                "columns": 1,
                "fields": [
                    {
                        "label": "Observations",
                        "name": "obs",
                        "value": null,
                        "meta": {
                            "custom": false,
                            "type": "input",
                            "content": "text",
                            "disabled": false
                        }
                    }
                ]
            }
        ]
    }
    ```

    Note that when giving a number of columns, the fields will be evenly divided into columns, and will have equal width. 
    If a custom value is given, then you may specify on each field the desired width.

2. Create and setup in your controller method the `Form` object, and return the resulting data. 
You may even use the available fluent methods to override (if necessary) default values provided in the template. 

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

3. Add inside your page/component

    For the regular form
    ````
    <vue-form class="box"
        :data="form">
    </vue-form>
    ````
    
    For the server-side variant
    ```
    <vue-form-ss class="box animated fadeIn"
        :params="[$route.name, $route.params.id, false]"        
        ref="form">
    </vue-form-ss>
    ```

### VueJS Components
The main `VueForm.vue` component takes the following parameters:
- `data`, object, represents the configuration used to render the form and its elements | required
- `params`, object, can be used to send additionnal parameters with the form request | default `null` | (optional)

Note: when sending extra parameters, on the back-end they can be accessed in the request's `_params` attribute.  

Note: when creating a resource and no redirect is given in the POST response, the form does not perform a redirect.

The `VueFormSs.vue` component takes the following parameter:
- `params`, array, parameters that are used for Ziggy `route` helper function, in order to do an ajax get request 
and fetch the form configuration | required 

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
- `hide(string $field)`, marks the field as hidden
- `disable(string $field)`, marks the field as disabled
- `readonly(string $field)`, marks the field as readonly
- `meta(string $field, string $param, $value)`, sets a specific value, for a meta param, for the given field
- `append($prop, $value)`, adds a property and its value in the template root-level `params` object, 
in order to make it available in the front-end  
- `authorize(bool $authorize)`, set the authorize flag for the form.
If this value is not given in the form, the global default value is taken from the config file 

### Configuration
The Form builder can be globally configured from within its own configuration file, found at `config/enso/forms.php`:
```php
    'validations' => 'local',
    'buttons' => [
        'create' => [
            'icon' => 'plus',
            'class' => 'is-info',
            'event' => 'create',
            'action' => 'router',
            'label' => 'Create',
        ],
        'store' => [
            'icon' => 'check',
            'class' => 'is-success',
            'event' => 'store',
            'action' => 'router',
            'label' => 'Save',
        ],
        'update' => [
            'icon' => 'check',
            'class' => 'is-success',
            'event' => 'update',
            'action' => 'router',
            'label' => 'Update',
        ],
        'destroy' => [
            'icon' => 'trash-alt',
            'class' => 'is-danger',
            'event' => 'destroy',
            'action' => 'ajax',
            'method' => 'DELETE',
            'message' => 'The selected record is about to be deleted. Are you sure?',
            'confirmation' => true,
            'label' => 'Delete',
        ],
    ],
    'authorize' => true,
    'dividerTitlePlacement' => 'center',
```  
The following options are available:
- `validations`, string, values may be 'local' or 'production'. If set to 'local', 
form configuration validations are performed only when developing locally, 
while 'production' will always perform the validation checks. 

    Note that the flag only affects the validation of the **template** not the validation of form input values, 
    which is always enabled
    
- `buttons`, array, enables the customization of various options for the buttons used in the forms, such as labels, 
    colors, events and more
- `authorize`, boolean, flag that enables the integration with the laravel-enso authorization, 
    meaning that certain user actions are not available if the user doesn't have access on the corresponding routes      
- `dividerTitlePlacement`, string, values may be 'left', 'center', 'right'. Affectes the placement of sections' divider text,
    if used and given within the template

### Publishes

- `php artisan vendor:publish --tag=form-assets` - the VueJS components,
- `php artisan vendor:publish --tag=form-config` - the configuration file,
- `php artisan vendor:publish --tag=forms` - the example JSON template file,
- `php artisan vendor:publish --tag=enso-assets` - a common alias for when wanting to update the VueJS components,
once a newer version is released, usually used with the `--force` flag

### Notes

For more examples, you may look into the [Enso](https://github.com/laravel-enso) packages for various use cases.

The [Laravel Enso](https://github.com/laravel-enso/Enso) package comes with this package included.

Depends on:
- [Helpers](https://github.com/laravel-enso/VueComponents) for utility objects


<!--h-->
### Contributions

are welcome. Pull requests are great, but issues are good too.

### License

This package is released under the MIT license.
<!--/h-->