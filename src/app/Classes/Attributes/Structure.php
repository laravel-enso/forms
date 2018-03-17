<?php

namespace LaravelEnso\FormBuilder\app\Classes\Attributes;

class Structure
{
    const Mandatory = ['method', 'sections'];
    const Optional = [
        'title', 'icon', 'routePrefix', 'actions', 'authorize', 'params', 'dividerTitlePlacement',
    ];

    const SectionMandatory = ['columns', 'fields'];
    const SectionOptional = ['divider', 'title'];

    const Columns = [1, 2, 3, 4, 6, 12];
}
