<?php

/**
 * Part of Omega - Validation Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Validation\Rule;

use Omega\Support\Str;

/**
 * Email rule class.
 *
 * The `EmailRule` class is responsible for validating email addresses. It checks if
 * the given input is a valid email address by verifying the presence of the '@' symbol.
 * If the input is empty, it is considered valid.
 *
 * @category   Omega
 * @package    Validation
 * @subpackage Rule
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class EmailRule extends AbstractRule
{
    /**
     * {@inheritdoc}
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
     */
    public function getMessage(array $data, string $field, array $params): string
    {
        return "{$field} should be an email.";
    }
}
