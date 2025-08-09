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
 * Abstract validation defines the contract for adding and validating rules.
 *
 * The `AbstractValidation` provides methods for adding validation rules and validating
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
abstract class AbstractValidation implements ValidationInterface
{
    /**
     * Rule array.
     *
     * @var array<string, RuleInterface> Holds an array of rules.
     */
    protected array $rules = [];

    /**
     * {@inheritdoc}
     */
    abstract public function addRule(string $alias, RuleInterface $rule): static;

    /**
     * {@inheritdoc}
     */
    abstract public function validate(array $data, array $rules, string $sessionName = 'errors'): array;
}
