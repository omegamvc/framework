<?php

/**
 * Part of Omega - Validation Package.
 *
 * @see        https://omegamvc.github.io
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\Validation\Rule;

/*
 * @use
 */
use Omega\Support\Str;
use Omega\Validation\Exception\ValidationException;

/**
 * Min rule class.
 *
 * The `MinRule` class is responsible for validating whether a given input string
 * has a minimum length. It checks if the input is empty and, if not, compares its
 * length with the specified minimum length parameter. If the input is empty, it is
 * considered valid.
 *
 * @category    Omega
 * @package     Validation
 * @subpackage  Rule
 *
 * @see        https://omegamvc.github.io
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class MinRule extends AbstractRule
{
    /**
     * {@inheritdoc}
     *
     * @param array<string,mixed> $data   Holds array of data.
     * @param string              $field  Holds the name of the field being validated.
     * @param array<string>       $params Holds an array of parameters (not used for this rule).
     *
     * @return string|bool Returns `true` if validation is successful (valid integer format), or an error message ifù
     *                     the validation fails.
     *
     * @throws ValidationException If the minimum length parameter is not specified.
     */
    public function validate(array $data, string $field, array $params): string|bool
    {
        $value = $data[$field] ?? '';

        if (empty($value)) {
            return true;
        }

        if (empty($params[0])) {
            throw new ValidationException('Specify a min length.');
        }

        if (!is_scalar($value)) {
            $value = '';
        } else {
            $value = (string)$value;
        }

        $length = (int)$params[0];

        return Str::strlen($value) >= $length;
    }

    /**
     * {@inheritdoc}
     *
     * @param array<string, mixed> $data   Holds an array of data.
     * @param string               $field  Holds the name of the field that failed validation.
     * @param array<string>        $params Holds an array of parameters (not used for this rule).
     *
     * @return string Returns the error message indicating that the field should be in a valid integer format.
     */
    public function getMessage(array $data, string $field, array $params): string
    {
        $length = (int)$params[0];

        return "{$field} should be at least {$length} characters.";
    }
}