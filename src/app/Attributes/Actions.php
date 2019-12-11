<?php

namespace LaravelEnso\Forms\app\Attributes;

class Actions
{
    public const Create = ['back', 'store'];
    public const Update = ['back', 'create', 'show', 'update', 'destroy'];
}
