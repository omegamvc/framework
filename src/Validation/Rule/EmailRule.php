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

/**
 * Email rule class.
 *
 * The `EmailRule` class is responsible for validating email addresses. It checks if
 * the given input is a valid email address by verifying the presence of the '@' symbol.
 * If the input is empty, it is considered valid.
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
class EmailRule extends AbstractRule
{
    /**
     * {@inheritdoc}
     *
     * @param array<string,mixed> $data   Holds array of data.
     * @param string              $field  Holds the name of the field being validated.
     * @param array<string>       $params Holds an array of parameters (not used for this rule).
     *
     * @return string|bool Returns `true` if validation is successful (valid integer format), or an error message if
     *                     the validation fails.
     */
    public function validate(array $data, string $field, array $params): string|bool
    {
        $value = $data[$field] ?? '';

        if (empty($value)) {
            return true;
        }

        if (!is_scalar($value)) {
            $value = '';
        } else {
            $value = (string)$value;
        }

        return Str::strContains($value, '@');
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
        return "{$field} should be an email.";
    }
}