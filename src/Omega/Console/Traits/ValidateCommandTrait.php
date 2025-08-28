<?php

/**
 * Part of Omega - Console Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Console\Traits;

use Exception;
use Omega\Console\Style\Alert;
use Omega\Console\Style\Style;
use Omega\Validator\Rule\ValidPool;
use Omega\Validator\Validator;

/**
 * ValidateCommandTrait provides a standardized way to validate command inputs
 * and retrieve validation messages for console output.
 *
 * @category   Omega
 * @package    Console
 * @subpackges Traits
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
trait ValidateCommandTrait
{
    /**
     * Validator instance used to validate input data.
     *
     * @var Validator
     */
    protected Validator $validate;

    /**
     * Initialize the validator with input data and attach validation rules.
     *
     * @param array<string, string|bool|int|null> $inputs Associative array of input values
     * @return void
     */
    protected function initValidate(array $inputs): void
    {
        $this->validate = new Validator($inputs);
        $this->validate->validation(
            fn (ValidPool $rules) => $this->validateRule($rules)
        );
    }

    /**
     * Define the validation rules for the current command.
     *
     * Override this method in the consuming class to add custom validation rules.
     *
     * @param ValidPool $rules The validation rule pool to populate
     * @return void
     */
    protected function validateRule(ValidPool $rules): void
    {
        // Implement validation rules in child class
    }

    /**
     * Check whether all validations passed successfully.
     *
     * @return bool True if all inputs are valid, false otherwise
     */
    protected function isValid(): bool
    {
        return $this->validate->isValid();
    }

    /**
     * Render validation error messages using a Style instance.
     *
     * Each validation error is displayed as a warning alert.
     *
     * @param Style $style Style instance used to render messages
     * @return Style Modified Style instance with validation messages appended
     * @throws Exception If any unexpected error occurs while rendering
     */
    protected function getValidateMessage(Style $style): Style
    {
        foreach ($this->validate->getError() as $input => $message) {
            $style->tap(
                Alert::render()->warn($message)
            );
        }

        return $style;
    }
}
