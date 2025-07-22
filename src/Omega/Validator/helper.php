<?php

declare(strict_types=1);

namespace Omega\Validator;

use Omega\Validator\Rule\Filter;
use Omega\Validator\Rule\Valid;

if (!function_exists('vr')) {
    /**
     * Alias for validation rule,
     * return string validation rule.
     *
     * @return Valid
     */
    function vr(): Valid
    {
        return new Valid();
    }
}

if (!function_exists('fr')) {
    /**
     * Alias for filter rule,
     * return string filter rule.
     *
     * @return Filter
     */
    function fr(): Filter
    {
        return new Filter();
    }
}

if (!function_exists('validate')) {
    /**
     * Alias for validator.
     *
     * @param array<string, mixed> $field Field input
     */
    function validate(array $field): Validator
    {
        return new Validator($field);
    }
}
