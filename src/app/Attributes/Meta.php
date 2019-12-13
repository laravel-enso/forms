<?php

namespace LaravelEnso\Forms\app\Attributes;

class Meta
{
    public const Mandatory = ['type'];

    public const Optional = [
        'options', 'multiple', 'custom', 'content', 'step', 'min', 'max', 'disabled', 'readonly',
        'hidden', 'source', 'format', 'altFormat', 'time', 'rows', 'placeholder', 'trackBy',
        'label', 'tooltip', 'symbol', 'precision', 'thousand', 'decimal', 'positive', 'negative',
        'zero', 'resize', 'translated', 'time12hr', 'disable-clear', 'params', 'pivotParams',
        'customParams', 'objects', 'toolbar', 'plugins',
    ];

    public const Types = [
        'input', 'select', 'datepicker', 'timepicker', 'textarea', 'password', 'wysiwyg',
    ];
}
