<?php

namespace LaravelEnso\FormBuilder\app\Classes\Attributes;

class Structure
{
    const Mandatory = ['method', 'sections', 'routeParams'];

    const Optional = [
        'title', 'icon', 'routePrefix', 'routes', 'actions',
        'authorize', 'params', 'dividerTitlePlacement', 'tabs',
    ];

    const SectionMandatory = ['columns', 'fields'];

    const SectionOptional = ['divider', 'title', 'column', 'tab'];

    const Columns = [1, 2, 3, 4, 6, 12, 'custom'];
}
