<?php

namespace LaravelEnso\Forms\app\Attributes;

class Structure
{
    const Mandatory = ['method', 'sections', 'routeParams'];

    const Optional = [
        'title', 'icon', 'routePrefix', 'routes', 'actions', 'autosave', 'debounce',
        'authorize', 'params', 'dividerTitlePlacement', 'tabs', 'labels',
    ];

    const SectionMandatory = ['columns', 'fields'];

    const SectionOptional = ['divider', 'title', 'column', 'tab', 'slot'];

    const Columns = [1, 2, 3, 4, 6, 12, 'custom', 'slot'];
}
