# Form Builder

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/a3cf79a9ca584f08b3be0246cb488788)](https://www.codacy.com/app/laravel-enso/FormBuilder?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=laravel-enso/FormBuilder&amp;utm_campaign=Badge_Grade)
[![StyleCI](https://styleci.io/repos/99695155/shield?branch=master)](https://styleci.io/repos/99695155)
[![License](https://poser.pugx.org/laravel-enso/formbuilder/license)](https://packagist.org/packages/laravel-enso/formbuilder)
[![Total Downloads](https://poser.pugx.org/laravel-enso/formbuilder/downloads)](https://packagist.org/packages/laravel-enso/formbuilder)
[![Latest Stable Version](https://poser.pugx.org/laravel-enso/formbuilder/version)](https://packagist.org/packages/laravel-enso/formbuilder)

JSON-based Form builder for [Laravel Enso](https://github.com/laravel-enso/Enso)

This package can work independently of the [Enso](https://github.com/laravel-enso/Enso) ecosystem.

The front end implementation that utilizes this api is present in the [forms](https://github.com/enso-ui/forms) package.

For live examples and demos, you may visit [laravel-enso.com](https://www.laravel-enso.com)

[![Watch the demo](https://laravel-enso.github.io/formbuilder/screenshots/bulma_109_thumb.png)](https://laravel-enso.github.io/formbuilder/videos/bulma_demo_01.mp4)

<sup>click on the photo to view a short demo in compatible browsers</sup>

## Installation

Comes pre-installed in Enso. 

To install outside of Enso:

1. install the package `composer require laravel-enso/formbuilder` 

2. if needed, publish and customize the config

3. install the api implementation for the front end: `yarm add @enso-ui/forms`

## Features

- allows for quick creation of forms
- uses a JSON template file for generating the form
- flexible form layout, that supports directly from the template
    * grouping inputs into logical sections and columns of different widths, even on the same row
    * grouping sections into tabs 
- for most forms, the json template is all that it's needed
- provides helpful error messages when the template is missing parameters or unexpected values are found
- when needed, allows the customization of form components in order to cover all scenarios
- comes with a `template.json` file that can be used as an example when starting out
- integrates with the [Laravel Request Validation](https://laravel.com/docs/5.7/validation#available-validation-rules) for seamless usage and reusability
- uses the Enso toast notifications for stylish feedback on the various actions
- customizable placeholder for all elements

### Configuration & Usage

Be sure to check out the full documentation for this package available at [docs.laravel-enso.com](https://docs.laravel-enso.com/backend/form-builder.html)

### Contributions

are welcome. Pull requests are great, but issues are good too.

### License

This package is released under the MIT license.
