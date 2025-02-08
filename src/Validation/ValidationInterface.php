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

namespace Omega\Validation;

use Omega\Validation\Rule\RuleInterface;
use Omega\Validation\Exception\ValidationException;

/**
 * Validation interface defines the contract for adding and validating rules.
 *
 * The `ValidationInterface` provides methods for adding validation rules and validating
 * data against those rules.
 *
 * @category  Omega
 * @package   Validation
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */
interface ValidationInterface
{
    /**
     * Add a validation rule to the validator.
     *
     * @param string        $alias Holds the alias for the rule.
     * @param RuleInterface $rule  Holds an instance of RuleInterface representing the rule.
     * @return $this
     */
    public function addRule(string $alias, RuleInterface $rule): static;

    /**
     * Validate data against a set of rules.
     *
     * @param array<string, mixed>              $data        Holds an array of data to validate.
     * @param array<string, array<int, string>> $rules       Holds an array of validation rules.
     * @param string                            $sessionName Holds the session name for storing validation errors.
     * @return array<string, mixed> Return an array containing valid data.
     * @throws ValidationException if validation fails.
     */
    public function validate(array $data, array $rules, string $sessionName = 'errors'): array;
}
