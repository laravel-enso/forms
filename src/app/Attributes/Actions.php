<?php

namespace LaravelEnso\Forms\app\Attributes;

class Actions
{
    const Create = ['back', 'store'];
    const Update = ['back', 'create', 'show', 'update', 'destroy'];
}
