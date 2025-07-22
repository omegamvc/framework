<?php

/**
 * Part of Omega - Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
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
 * Trait ValidateCommandTrait
 *
 * Provides input validation support for console commands using the Validator component.
 * Allows commands to define custom validation rules, check input validity,
 * and render validation error messages using console styling.
 *
 * @category   Omega
 * @package    Console
 * @subpackage Traits
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
trait ValidateCommandTrait
{
    /**
     * Instance of the validator used for validating user input.
     *
     * @var Validator
     */
    protected Validator $validate;

    /**
     * Initialize the validator with input data and define validation rules.
     *
     * @param array<string, string|bool|int|null> $inputs The input data to validate.
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
     * Define custom validation rules.
     *
     * This method should be overridden by the class using the trait
     * to apply specific rules to the input fields.
     *
     * @param ValidPool $rules The rule pool to define validation rules on.
     * @return void
     */
    protected function validateRule(ValidPool $rules): void
    {
    }

    /**
     * Check whether the input data passed validation.
     *
     * @return bool True if validation passed; false otherwise.
     */
    protected function isValid(): bool
    {
        return $this->validate->isValid();
    }

    /**
     * Render validation error messages using the provided style instance.
     *
     * @param Style $style The console style instance used for rendering.
     * @return Style The modified style instance with rendered messages.
     * @throws Exception If rendering fails.
     */
    protected function getValidateMessage(Style $style): Style
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($this->validate->getError() as $input => $message) {
            $style->tap(
                Alert::render()->warn($message)
            );
        }

        return $style;
    }
}
