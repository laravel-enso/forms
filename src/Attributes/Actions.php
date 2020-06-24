<?php

namespace LaravelEnso\Forms\Attributes;

class Actions
{
    public const Create = ['back', 'store'];
    public const Update = ['back', 'create', 'show', 'update', 'destroy'];
}
