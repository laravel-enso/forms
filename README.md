<!--h-->
# FormBuilder

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/a3cf79a9ca584f08b3be0246cb488788)](https://www.codacy.com/app/laravel-enso/FormBuilder?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=laravel-enso/FormBuilder&amp;utm_campaign=Badge_Grade)
[![StyleCI](https://styleci.io/repos/99695155/shield?branch=master)](https://styleci.io/repos/99695155)
[![License](https://poser.pugx.org/laravel-enso/formbuilder/license)](https://https://packagist.org/packages/laravel-enso/formbuilder)
[![Total Downloads](https://poser.pugx.org/laravel-enso/formbuilder/downloads)](https://packagist.org/packages/laravel-enso/formbuilder)
[![Latest Stable Version](https://poser.pugx.org/laravel-enso/formbuilder/version)](https://packagist.org/packages/laravel-enso/formbuilder)
<!--/h-->

JSON-based Form builder for [Laravel Enso](https://github.com/laravel-enso/Enso)

[![Watch the demo](https://laravel-enso.github.io/formbuilder/screenshots/Selection_109_thumb.png)](https://laravel-enso.github.io/formbuilder/videos/demo_01.webm)

<sup>click on the photo to view a short demo in compatible browsers</sup>

### Features
- allows for quick creation of forms
- uses a JSON template file for generating the form
- uses [Laravel Enso](https://github.com/laravel-enso/Enso) improved elements, such as the `vue-select` and `datepicker`
- for most forms, the json template is all that it's needed
- when needed, allows the customization of form components in order to cover all scenarios
- comes with `template.json` file that can be used as an example when starting out
- permits cleaning the entire form or reverting to the original values (when editing) with one click
- integrates with the Laravel Request Validation for seamless usage and usability

### Under the Hood
- a template file is needed in order to generate the form data structure object
- the `FormBuilder` object has to be used in the back-end (controller) to parse the template, get additional parameters if needed, and build the structure
- a `VueForm` object needs to be included in the view/page/parent component, taking the form-builders resulting object as parameter 

### Notes
Depends on:
- [select](https://github.com/laravel-enso/Select) for rendering select elements
- [vuecomponents](https://github.com/laravel-enso/VueComponents) for rendering timepicker and datepicker elements 
- [vueadminlte](https://github.com/laravel-enso/VueAdminLTE) for the box element encasing the form

### Installation Steps
1. Add `LaravelEnso\FormBuilder\FormBuilderServiceProvider::class` to `config/app.php`

2. Publish the VueJS components file with `php artisan vendor:publish --tag=forms-component`.

3. (optional) Publish the template file with `php artisan vendor:publish --tag=forms-template`.

4. Based on your workflow, be sure to include/require the new components so that they're available to Vue. Compile with `gulp` / `npm run dev` / etc.

````js
Vue.component('documents', require('./vendor/laravel-enso/components/vueforms/VueForm.vue'));
Vue.component('document', require('./vendor/laravel-enso/components/vueforms/VueFormInput.vue'));
````

### Usage

1. Create a template file for the new form, using `template.json` as an example, and place it inside `app/Forms` (recommended)
5. Create and setup in your controller method the `FormBuilder` object, and get the resulting data

````php
$form = (new FormBuilder(app_path('Forms/owner.json')))
            ->setTitle('Create a new Owner')
            ->setAction('POST')
            ->setUrl('/administration/owners')
            ->setSelectOptions('role_list', Role::pluck('name', 'id'))
            ->getData(); 
            
return view('administration.owners.create', compact('form'));
```` 

6. Add to you blade

````
<vue-form :data="{!! $form !!}">
</vue-form>
````

### Publishes

- `php artisan vendor:publish --tag=forms-component` - the VueJS components
- `php artisan vendor:publish --tag=forms-template` - the JSON template file
- `php artisan vendor:publish --tag=enso-update` - a common alias for when wanting to update the VueJS components,
once a newer version is released

<!--h-->
### Contributions

are welcome. Pull requests are great, but issues are good too.

### License

This package is released under the MIT license.
<!--/h-->