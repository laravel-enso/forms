<?php

namespace LaravelEnso\Forms\Attributes;

class Structure
{
    public const Mandatory = ['method', 'sections', 'routeParams'];

    public const Optional = [
        'title', 'icon', 'routePrefix', 'routes', 'actions', 'autosave', 'debounce',
        'authorize', 'params', 'dividerTitlePlacement', 'tabs', 'labels',
    ];

    public const SectionMandatory = ['columns', 'fields'];

    public const SectionOptional = ['divider', 'title', 'column', 'tab', 'slot', 'hidden'];

    public const Columns = [1, 2, 3, 4, 6, 12, 'custom', 'slot'];
}
