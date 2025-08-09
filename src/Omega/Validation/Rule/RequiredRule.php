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

/**
 * Required rule class.
 *
 * The `RequiredRule` class is responsible for validating whether a given input field
 * is required and not empty. It checks if the input is empty and returns `true` if it's
 * not, indicating that the input is valid.
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
class RequiredRule extends AbstractRule
{
    /**
     * {@inheritdoc}
     */
    public function validate(array $data, string $field, array $params): string|bool
    {
        return ! empty($data[$field]);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage(array $data, string $field, array $params): string
    {
        return "{$field} is required";
    }
}
