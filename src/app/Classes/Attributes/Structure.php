<?php

namespace LaravelEnso\FormBuilder\app\Classes\Attributes;

class Structure
{
    const Mandatory = ['columns', 'method', 'fields'];

    const Optional = ['title', 'icon', 'routePrefix', 'actions', 'authorize', 'params'];
}
