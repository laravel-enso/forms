<?php

namespace LaravelEnso\Forms\app\Attributes;

class Meta
{
    const Mandatory = ['type'];

    const Optional = [
        'options', 'multiple', 'custom', 'content', 'step', 'min', 'max', 'disabled', 'readonly',
        'hidden', 'source', 'format', 'altFormat', 'time', 'rows', 'placeholder', 'trackBy', 'label', 'tooltip',
        'symbol', 'precision', 'thousand', 'decimal', 'positive', 'negative', 'zero', 'resize',
        'translated', 'time12hr', 'disable-clear', 'params', 'objects',
    ];

    const Types = [
        'input', 'select', 'datepicker', 'timepicker', 'textarea', 'password', 'wysiwyg',
    ];
}
