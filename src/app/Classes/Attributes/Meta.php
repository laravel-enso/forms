<?php

namespace LaravelEnso\FormBuilder\app\Classes\Attributes;

class Meta
{
    const Mandatory = ['type'];

    const Optional = [
        'options', 'multiple', 'custom', 'content', 'step', 'min', 'max',
        'disabled', 'readonly', 'hidden', 'source', 'format', 'time', 'rows',
        'placeholder', 'trackBy', 'label', 'tooltip', 'symbol', 'precision',
        'thousand', 'decimal', 'positive', 'negative', 'zero',
    ];

    const Types = ['input', 'select', 'datepicker', 'timepicker', 'textarea'];
}
