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

namespace Omega\Validation;

/*
 * @use
 */
use Omega\Validation\Rule\RuleInterface;
use Omega\Validation\Exception\ValidationException;

/**
 * Abstract validation defines the contract for adding and validating rules.
 *
 * The `AbstractValidation` provides methods for adding validation rules and validating
 * data against those rules.
 *
 * @category    Omega
 * @package     Validation
 *
 * @see        https://omegamvc.github.io
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
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
     *
     * @param string        $alias Holds the alias for the rule.
     * @param RuleInterface $rule  Holds an instance of RuleInterface representing the rule.
     *
     * @return $this
     */
    abstract public function addRule(string $alias, RuleInterface $rule): static;

    /**
     * {@inheritdoc}
     *
     * @param array<string, mixed>              $data        Holds an array of data to validate.
     * @param array<string, array<int, string>> $rules       Holds an array of validation rules.
     * @param string                            $sessionName Holds the session name for storing validation errors.
     *
     * @return array<string, mixed> Return an array containing valid data.
     *
     * @throws ValidationException if validation fails.
     */
    abstract public function validate(array $data, array $rules, string $sessionName = 'errors'): array;
}
