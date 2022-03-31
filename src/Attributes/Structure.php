<?php

namespace LaravelEnso\Forms\Attributes;

class Structure
{
    public const Mandatory = ['method', 'sections', 'routeParams'];

    public const Optional = [
        'actions', 'authorize', 'autosave', 'clearErrorsControl', 'debounce',
        'dividerTitlePlacement', 'hiddenActions', 'icon', 'labels', 'params',
        'routePrefix', 'routes', 'tabs', 'title',
    ];

    public const SectionMandatory = ['columns', 'fields'];

    public const SectionOptional = ['divider', 'title', 'column', 'tab', 'slot', 'hidden'];

    public const Columns = ['custom', 'slot'];
}
