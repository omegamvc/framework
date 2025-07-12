<?php

/**
 * Part of Omega - Validation Package.
 * php versio 8.2
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0  https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

declare(strict_types=1);

namespace Omega\Validation\Rule;

/**
 * RuleInterface.
 *
 * The `RuleInterface` defines the contract that validation rules in Omega must
 * adhere to.
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
interface RuleInterface
{
    /**
     * Validate the rule.
     *
     * @param array<string,mixed> $data   Holds array of data.
     * @param string              $field  Holds the name of the field being validated.
     * @param array<string>       $params Holds an array of parameters (not used for this rule).
     * @return string|bool Returns `true` if validation is successful (valid integer format), or an error message if
     *                     the validation fails.
     */
    public function validate(array $data, string $field, array $params): string|bool;

    /**
     * Get the validation error message.
     *
     * @param array<string, mixed> $data   Holds an array of data.
     * @param string               $field  Holds the name of the field that failed validation.
     * @param array<string>        $params Holds an array of parameters (not used for this rule).
     * @return string Returns the error message indicating that the field should be in a valid integer format.
     */
    public function getMessage(array $data, string $field, array $params): string;
}
