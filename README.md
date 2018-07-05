<!--h-->
# FormBuilder

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/a3cf79a9ca584f08b3be0246cb488788)](https://www.codacy.com/app/laravel-enso/FormBuilder?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=laravel-enso/FormBuilder&amp;utm_campaign=Badge_Grade)
[![StyleCI](https://styleci.io/repos/99695155/shield?branch=master)](https://styleci.io/repos/99695155)
[![License](https://poser.pugx.org/laravel-enso/formbuilder/license)](https://packagist.org/packages/laravel-enso/formbuilder)
[![Total Downloads](https://poser.pugx.org/laravel-enso/formbuilder/downloads)](https://packagist.org/packages/laravel-enso/formbuilder)
[![Latest Stable Version](https://poser.pugx.org/laravel-enso/formbuilder/version)](https://packagist.org/packages/laravel-enso/formbuilder)
<!--/h-->

JSON-based Form builder for [Laravel Enso](https://github.com/laravel-enso/Enso)

[![Watch the demo](https://laravel-enso.github.io/formbuilder/screenshots/bulma_109_thumb.png)](https://laravel-enso.github.io/formbuilder/videos/bulma_demo_01.mp4)

<sup>click on the photo to view a short demo in compatible browsers</sup>

### Features

- allows for quick creation of forms
- uses a JSON template file for generating the form
- flexible form layout, that supports grouping inputs into logical sections and columns of different widths, 
even on the same row 
- uses it's own VueJS components, such as `vue-select` and `datepicker` for an improved experience
-`VueFormSs.vue` a server-side form wrapper is available that can be used to fetch the form configuration 
- for most forms, the json template is all that it's needed
- provides helpful error messages when the template is missing parameters or unexpected values are found
- when needed, allows the customization of form components in order to cover all scenarios
- comes with a `template.json` file that can be used as an example when starting out
- integrates with the Laravel Request Validation for seamless usage and reusability
- uses the Enso toast notifications for stylish feedback on the various actions
- customizable placeholder for all elements
- handles number, money and currency formatting, using the [accounting.js](http://openexchangerates.github.io/accounting.js/) library
- provides beautiful date & time selection, based on the [flatpickr](https://github.com/flatpickr/flatpickr) library  

### Under the Hood

- a template file is needed in order to generate the form data structure object
- the `Form` object has to be used in the back-end (controller) to parse the template, 
get additional parameters if needed, and build the structure
- although in most common scenarios you can give all the required configuration in the template file, 
the `Form` class has fluent helper functions for setting/overriding most attributes
- a `VueForm` component needs to be included in the view/page/parent component, 
taking the form-builder's resulting object as parameter
- a `VueFormSs` (ss stands for server-side) component should be included in the view/page/parent component, 
taking the route params needed to make the ajax request and fetch the form configuration

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
    ```json
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
                        "options": [],
                        "placeholder": "Type it in"
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
                            "disabled": false,
                            "placeholder": "Street is manadatory",
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
                "columns": 2,
                "fields": [
                    {
                        "label": "Observations",
                        "name": "obs",
                        "value": null,
                        "meta": {
                            "custom": false,
                            "type": "input",
                            "content": "text",
                            "disabled": false,
                            "disabled": "Any comments go here",
                        }
                    },
                    {
                         "label": "Price",
                         "name": "price",
                         "value": 12321.12,
                         "meta": {
                            "type": "input",
                            "content": "money",
                            "symbol": "GBP", 
                            "precision": "3", 
                            "thousand": " ", 
                            "decimal": ",", 
                            "positive": "%s %v", 
                            "negative": "%s (%v)", 
                            "zero":"%s --"  
                         }  
                    }
                ]
            }
        ]
    }
    ```

    Note that when giving a number of columns, the fields will be evenly divided into columns, and will have equal width. 
    If a custom value is given, then you may specify on each field the desired width. See below for more information.

    Note that when using the money input type, you should read the 
    [accounting.js](http://openexchangerates.github.io/accounting.js/) documentation, as these details are 
    outside of the scope of this documentation. 

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
        :route-params="[$route.name, $route.params.id, false]"
        :params="params"
        ref="form">
    </vue-form-ss>
    ```

### VueJS Components

The main `VueForm.vue` component takes the following parameters:
- `data`, object, represents the configuration used to render the form and its elements | required
- `params`, object, can be used to send additionnal parameters with the form request | default `null` | (optional)
- `i18n`, function, a translation/internationalization function, that can be used when if using the component outside 
of the Enso ecosistem. By default, it attempts to use the Enso `__` translation function if available
- `locale`, string, the locale to be used by the various sub-components  | default `en` | (optional)

Note: when sending extra parameters, on the back-end they can be accessed in the request's `_params` attribute.  

Note: when creating a resource and no redirect is given in the POST response, the form does not perform a redirect.

The `VueFormSs.vue` component takes the following parameter:
- `params`, array, parameters that are used for Ziggy `route` helper function, in order to do an ajax get request 
and fetch the form configuration | required 
- `locale`, string, the locale to be used by the various sub-components. Within Enso, it attempts to read and use
the user's language preferences from within the Vuex store

### Advanced usage

The PHP `Form` class provides the following fluent helper functions:
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

It also provides the 2 methods used for generating the properly formatted form configuration:
- `create($model)`, for a create-type form, where the model is optional. If given, the model attribute values 
are filled for the form values (another way of setting some default values)
- `edit($model)`. for an edit-type form, where the model is required. The model's values are set as the form values  

### Global Configuration

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

### Form Configuration
#### Root level parameters
```json
    "title": "Form Title",
    "icon": "icon",    
    "routePrefix": "administration.users",
    "authorize": true,
    "dividerTitlePlacement"
    "params": null,
    "actions": ["create", "store", "update", "destroy" ],
    "method": null,
    "sections": []
```

##### method
- Is: optional/required 
- Type: string
- Values: "post", "patch", "put" 

If using the form normally, by calling `create($model)`, `edit($model)` methods, 
then the action is set automatically, as "post" for creation and "patch" for edit. 
If using the form by calling the `build()` method, then you must make sure the method parameter is set.
 
##### sections
- Is: required 
- Type: array of objects

All form inputs are grouped into sections. See below for the sections details.

##### title
- Is: optional 
- Type: string 

This is the title of the form.

##### icon
- Is: optional 
- Type: string

The Font Awesome 5 icon class, for example `"book"` for the `"fa-book"` CSS class.

##### routePrefix
- Is: optional 
- Type: string

Represents the route prefix that is used when checking permissions and building the route/path for a certain button. 
For example, for a user form's Save button, if the name of the store route is `"administration.users.create"`, 
then the prefix is `"administration.users"` and the action is `"create"`.
 
##### authorize
- Is: optional 
- Type: boolean 

Flag that sets whether authorization checks should be made. 
If not given in the form, the option is read from the global form configuration, found at `config/enso/forms.php`

##### dividerTitlePlacement
- Is: optional 
- Type: string
- Value: may be one of `"left"`, `"center"`, `"right"` 

It specifies the relative position of the divider. 
If not given, the option is read from the global form configuration, found at `config/enso/forms.php`

##### params
- Is: optional 
- Type: object
 
Can be used to pass extra parameters to the VueJS component, useful when customizing the form in-page (with slots, 
linking the form component/data to other components in the page, etc). 

##### actions
- Is: optional 
- Type: array of strings, 
- Values: `"create"`, `"store"`, `"update"`, `"destroy"` 

The actions are used to determine the available buttons in the form. 
Note that if the `authorize` flag is set to true, the builder also checks if the user has acces to/for a certain action,
 and if he does not, the respective button won't be shown.  
If the actions are not given, defaults are used, depeding on the `method` parameter, as follows: 
 - if doing a POST, the actions array is `['store']`
 - if doing anything else, i.e. a PUT, the actions array is `["create", "update", "destroy"]`     

#### Section
The section is the organizing block for form inputs.

```json
"sections": 
    [
        {
            "columns": 3,
            "fields": [
                {
                    "label": "Country",
                    "name": "country_id",
                    "value": null,
                    "meta": {
                        "type": "select",
                        "multiple": false,
                        "source": "core.addresses.countriesSelectOptions",
                        "options": []
                    }
                },
                ...
            ]
        }, 
        {
            "columns": 6,
            "fields": [
                {
                    "label": "Number",
                    "name": "number",
                    "value": null,
                    "meta": {
                        "custom": false,
                        "type": "input",
                        "content": "text",
                        "disabled": false
                    }
                },
                ...
            ]
        },
        ...
    ]
``` 

##### columns
- Is: required
- Type: number/string
- Values: one of the following `1`, `2`, `3`, `4`, `6`, `12`, `"custom"`

The attribute specifies how many columns will be used for the form elements in this section. If giving a number, then 
the size of each element is calculated automatically. 

If using `"custom"`, you need to specify for each filed the column size, by providing the `column` parameter (see below).

##### fields
- Is: required
- Type: array of objects

The fields parameter will hold the actual form elements. For the configuration of each specific form element, see below.

##### divider
- Is: optional
- Type: boolean

Flag that specifies that a divider should be used here.

##### title
- Is: optional
- Type: string

Title for the divider. Should be used in conjunction with the `divider` parameters, 
as without setting the `divider` to `true`, the title will not be shown. 

Note that the position of the divider title will depend on the value of the `dividerTitlePlacement` parameter (see above).

#### Field
Is the individual element of the from, generally representing an input of some sort.

##### label
- Is: required
- Type: string

The label for the element.

##### name
- Is: required
- Type: string

The name of the Model's attribute, that is to be mapped to this input 
(for instance, the name is also used to fill the models's value when setting up an edit type of form).
 
The name will be the request's key for the value of the input given be the user, when an action is commited 
(for instance the user clicks the Save button). 

##### value
- Is: required
- Type: number/string/object/boolean

The starting value for a form element. The value can be 
- hard coded in the template, 
- it will be filled from the Model when creating an edit form (or a create form with the optional model parameter)
- it can also be set programatically by calling the Form object's `value()` method.

##### meta
- Is: required
- Type: object

Holds various mostly optional parameters that can be used to configure a form element (see Meta below.)

##### column
- Is: required
- Type: number

The size of the column for that element **IF** using the `"custom"` value for section `columns` parameter. 
The given number is used in combination with Bulma's `is-`x 12-columns-system. 
See [here](https://bulma.io/documentation/columns/sizes/) for more information.

Note that if `columns` parameter is not set to "custom", the `column` parameter is not required and is ignored.

#### Meta
Is a set of parameters used to configure the supported form elements.

##### type
- Is: required
- Type: string
- Value: one of the following `"input"`, `"select"`, `"datepicker"`, `"timepicker"`, `"textarea"`

##### content
- Is: optional
- Type: string
- Applies to: `"input"`

Represents the type for an <input> HTML element, and therefore can take the expected types such as `"text"`, `"number"`, `"date"`, `"checkbox"`, etc.
Can also take `"money"` (for monetary values inputs).  

##### disabled
- Is: optional
- Type: boolean

Flag that marks the disabled state for a form element. 

##### readonly
- Is: optional
- Type: boolean

Flag that marks the readonly state for a form element. 

##### placeholder
- Is: optional
- Type: string

The placeholder text used on that form element.

##### tooltip
- Is: optional
- Type: string

Tooltip used for that form element.

##### hidden
- Is: optional
- Type: boolean

Flag that marks the element as hidden, which means it will be rendered but will not be visible. 

##### custom
- Is: optional
- Type: boolean

Flag that marks this element as as CUSTOM. What this means is that the VueJS component does not attempt to insert an 
component for that element, but instead renders a named slot (the name being the element's `name`).

This allows you to build and insert custom elements in the form, for complex scenarios. 

##### options
- Is: optional
- Type: array of objects
- Applies to: `"select"`

Is an array of options for that select element.

##### trackBy
- Is: optional
- Type: string
- Applies to: `"select"`
- Default: `id`

Is the attribute that is to be used as identifier for each of the select options i.e. the name of the attribute that is 
to be used when setting the value for the 'value' attribute of an HTML `<option>` element. 

##### label
- Is: optional
- Type: string
- Applies to: `"select"`
- Default: `name`

Is the attribute that is to be used as label for each of the select options i.e. the name of the attribute that is 
to be used when setting the value for the an HTML `<option>` element. 

##### multiple
- Is: optional
- Type: boolean
- Applies to: `"select"`

Flag that determines the select element to accept multiple values (works as a multiselect).

##### source
- Is: optional
- Type: string
- Applies to: `"select"`

Flag that determines the select element to work in serverside mode, meaning that it will use the source URI in order to
fetch the list of options. When using the `source` parameter, the `options` parameter is not required. 

##### step
- Is: optional
- Type: numeric
- Applies to: `"input"`

Parameter corresponds to the step parameter for an HTML <input> field.

##### min
- Is: optional
- Type: numeric
- Applies to: `"input"`

Parameter corresponds to the min parameter for an HTML `<input>` field, where the browser does a client side validation.

##### max
- Is: optional
- Type: numeric
- Applies to: `"input"`

Parameter corresponds to the max parameter for an HTML `<input>` field, where the browser does a client side validation.

##### format
- Is: optional
- Type: string
- Applies to: `"datepicker"`, `"timepicker"`

Represents the format of the date/time used for the component.

Since the [flatpickr](https://github.com/flatpickr/flatpickr) library is used, it requires its format. For more details, 
check the [documentation](https://flatpickr.js.org/formatting/).

##### time
- Is: optional
- Type: boolean
- Applies to: `"datepicker"`

Flag that enables the time picking functionality for the datepicker, in addition to the default date functionality

##### rows
- Is: optional
- Type: numeric
- Applies to: `"textarea"`

##### resize
- Is: optional
- Type: boolean
- Applies to: `"textarea"`

Specifies the number of rows for the textarea.

##### symbol
- Is: optional
- Type: string
- Applies to: a `"money"`-type `"input"`

Is the currenct symbol to be used for a money input, for example `"$"`.

##### precision
- Is: optional
- Type: string
- Applies to: a `"money"`-type `"input"`

Is the precision (decimal places) for the amount.

##### thousand
- Is: optional
- Type: string
- Applies to: a "money"-type `"input"`

Is the thousands separator for the amount.

##### decimal
- Is: optional
- Type: string
- Applies to: a `"money"`-type `"input"`

Is the decimal separator for the amount.

##### positive
- Is: optional
- Type: string
- Applies to: a `"money"`-type `"input"`

The format for positive amounts, e.g. `"%s %v"`
See the [accounting.js](http://openexchangerates.github.io/accounting.js/) library for more.

##### negative
- Is: optional
- Type: string
- Applies to: a `"money"`-type `"input"`

The format for negative amounts, e.g. `"%s (%v)"`
See the [accounting.js](http://openexchangerates.github.io/accounting.js/) library for more.

##### zero
- Is: optional
- Type: string
- Applies to: a `"money"`-type `"input"`

The format for zero amounts, e.g. `"%s  -- "`
See the [accounting.js](http://openexchangerates.github.io/accounting.js/) library for more.


### Examples
Following you will find several non-exhaustive examples, with most if not all of the types, and various parameter 
combinations. 

#### Text input
A disabled generic text input
```json
{
    "label": "Description",
    "name": "description",
    "value": null,
    "meta": {
        "type": "input",
        "content": "text",
        "disabled": true
    }
}
```

#### Numeric input
A numeric text input with a 1-5 range, and a 0.5 step when changing values
```json
{
    "label": "Temperature",
    "name": "temp",
    "value": null,
    "meta": {
        "type": "input",
        "content": "number",
        "min": 1,
        "max": 5,
        "step": 0.5
    }
}
```

#### Checkbox input
A checkbox input, with a default value of true.
```json
{
    "label": "Is Enabled",
    "name": "is_enabled",
    "value": true,
    "meta": {
        "type": "input",
        "content": "checkbox"        
    }
}
```

#### Textarea
A textarea with a placeholder and a 5 rows height. Note that the textarea is resizable only if you add the `"resize": true` property.
```json
{
    "label": "Story",
    "name": "story",
    "value": null,
    "meta": {
        "type": "textarea",
        "placeholder": "We'd love to hear your story",
        "rows": 5,
        "resize": true
    }
}
```

#### DatePicker
The most basic datepicker, with a placeholder.
```json
{
    "label": "Start Date",
    "name": "start_date",
    "value": null,
    "meta": {
        "type": "datepicker",
        "placeholder": "Woot"
    }
}
```

#### DatePicker with time
A datepicker also with time selection.
```json
{
    "label": "Pick Up",
    "name": "pick_up",
    "value": null,
    "meta": {
        "type": "datepicker",
        "time": true,
        "format": "m/d/Y h:m"
    }
}
```

#### Timepicker
A timepicker with a placeholder and 24 hour format time. Note that if you use a 12 hour format time, on change, 
in the back end, you won't be able to differentiate between AM and PM. 
```json
{
    "label": "Reminder",
    "name": "reminder",
    "value": "13:59",
    "meta": {
        "type": "timepicker",
        "format": "H:i",
        "placeholder": "Select the time"
    }
}
```

#### Single Select
A single select, with a default non-standard option list, a set value, and custom tracking attributes.
```json
{
    "label": "Country",
    "name": "country_id",
    "value": "a",
    "meta": {
        "type": "select",
        "multiple": false,
        "options": [{"slug":"a", "customLabel":"First"},{"slug":"b", "customLabel":"Second"}],
        "trackBy":"slug",
        "label":"customLabel"
    }
}
```

#### Server Side Select
A server side single select, that fetches the list of options using the named route given as source.
```json
{
    "label": "Country",
    "name": "country_id",
    "value": null,
    "meta": {
        "type": "select",
        "multiple": false,
        "source": "core.addresses.countriesSelectOptions",
    }
}
```

#### Multi-Select
A multi select, with no default value, no options and no server-side fetching option.
```json
{
    "label": "Types",
    "name": "type_id",
    "value": [],
    "meta": {
        "type": "select",
        "multiple": true,
        "options": []
    }
}
```

In this case, you would set the options list from within your controller/service/etc by calling the options method
on the form builder object:

```php
$form->options('type_id', MyTypes::all())
```

Note: For more examples, you may look into the [Enso](https://github.com/laravel-enso) packages for various use cases.

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
- [flatpickr](https://github.com/flatpickr/flatpickr) for date/time selection
- [accounting.js](http://openexchangerates.github.io/accounting.js/) for currency formatting

<!--h-->
### Contributions

are welcome. Pull requests are great, but issues are good too.

### License

This package is released under the MIT license.
<!--/h-->