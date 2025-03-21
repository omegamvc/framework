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

use function is_int;
use function is_numeric;

/**
 * Integer rule class.
 *
 * The `IntegerRule` class is responsible for validating whether a given input is a valid
 * integer. It checks if the input is empty and, if not, verifies if it is numeric and if
 * it can be converted to an integer. If the input is empty, it is considered valid.
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
class IntegerRule extends AbstractRule
{
    /**
     * {@inheritdoc}
     */
    public function validate(array $data, string $field, array $params): string|bool
    {
        if (empty($data[$field])) {
            return true;
        }

        return is_numeric($data[$field]) && is_int((int)$data[$field]);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage(array $data, string $field, array $params): string
    {
        return "{$field} should be an integer.";
    }
}
