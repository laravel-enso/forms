<?php

namespace LaravelEnso\Forms\Exceptions;

use LaravelEnso\Helpers\Exceptions\EnsoException;

class Template extends EnsoException
{
    public static function missingRootAttributes($attrs)
    {
        return new static(__(
            'Mandatory attribute(s) missing: ":attrs"',
            ['attrs' => $attrs]
        ));
    }

    public static function unknownRootAttributes($attrs)
    {
        return new static(__(
            'Unknown attribute(s) found: ":attrs"',
            ['attrs' => $attrs]
        ));
    }

    public static function missingRoutePrefix($action)
    {
        return new static(__(
            '"routePrefix" attribute is missing and no route for action :action was provided',
            ['action' => $action]
        ));
    }

    public static function missingRoute($route)
    {
        return new static(__(
            'Route does not exist: :route',
            ['route' => $route]
        ));
    }

    public static function invalidActionsFormat()
    {
        return new static(__('"actions" attribute must be an array'));
    }

    public static function invalidParamsFormat()
    {
        return new static(__('"params" attribute must be an object'));
    }

    public static function invalidSectionFormat()
    {
        return new static(__('"section" attribute must be an array'));
    }

    public static function missingSectionAttributes($attrs)
    {
        return new static(__(
            'Mandatory attribute(s) missing from section object: ":attrs"',
            ['attrs' => $attrs]
        ));
    }

    public static function unknownSectionAttributes($attrs)
    {
        return new static(__(
            'Unknown attribute(s) found in section object: ":attrs"',
            ['attrs' => $attrs]
        ));
    }

    public static function missingTabAttribute($columns)
    {
        return new static(__(
            '"tab" attribute is missing on the following columns :columns',
            ['columns' => $columns]
        ));
    }

    public static function invalidColumnsAttributes($columns, $allowed)
    {
        return new static(__(
            'Invalid "columns" attribute in section object: :columns. Allowed values: :allowed or positive number',
            ['columns' => $columns, 'allowed' => $allowed]
        ));
    }

    public static function invalidFieldsFormat()
    {
        return new static(__('The fields attribute must be an array (of objects)'));
    }

    public static function missingFieldColumn($field)
    {
        return new static(__(
            'Missing "column" mandatory attribute (on custom columns) from the field: ":field".',
            ['field' => $field]
        ));
    }

    public static function invalidFieldColumn($field)
    {
        return new static(__(
            'Invalid "column" value found for field: :field. Allowed values from 1 to 12',
            ['field' => $field]
        ));
    }

    public static function fieldMissing($field)
    {
        return new static(__(
            'The :field field is missing from the JSON template',
            ['field' => $field]
        ));
    }

    public static function unknownActions($actions, $allowed)
    {
        return new static(__(
            'Incorrect action(s) provided: :actions. Allowed actions are: :allowed',
            ['actions' => $actions, 'allowed' => $allowed]
        ));
    }

    public static function missingFieldAttributes($attrs)
    {
        return new static(__(
            'Mandatory Field Attribute(s) Missing: ":attrs"',
            ['attrs' => $attrs]
        ));
    }

    public static function missingSelectMetaAttribute($field)
    {
        return new static(__(
            'Mandatory "source" or "option" meta parameter is missing for the :field select field',
            ['field' => $field]
        ));
    }

    public static function missingMetaAttributes($field, $attrs)
    {
        return new static(__(
            'Mandatory Meta Attribute(s) Missing: ":attrs" from field: :field',
            ['attrs' => $attrs, 'field' => $field]
        ));
    }

    public static function unknownMetaAttributes($field, $attrs)
    {
        return new static(__(
            'Unknown Attribute(s) Found: ":attrs" in field: :field',
            ['attrs' => $attrs, 'field' => $field]
        ));
    }

    public static function invalidFieldType($type)
    {
        return new static(__(
            'Unknown Field Type Found: :type',
            ['type' => $type]
        ));
    }

    public static function missingInputAttribute($field)
    {
        return new static(__(
            'Mandatory "type" meta parameter is missing for the :field input field',
            ['field' => $field]
        ));
    }

    public static function invalidSelectOptions($field)
    {
        return new static(__(
            '"options" meta parameter for field ":field" must be an array a collection or an Enum',
            ['field' => $field]
        ));
    }

    public static function invalidCheckboxValue($field)
    {
        return new static(__(
            'Chexboxes must have a boolean default value: ":field"',
            ['field' => $field]
        ));
    }

    public static function invalidSelectValue($field)
    {
        return new static(__(
            'Multiple selects must have an array default value: ":field"',
            ['field' => $field]
        ));
    }
}
