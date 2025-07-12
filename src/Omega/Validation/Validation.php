<?php

/**
 * Part of Omega - Validation Package.
 * php version 1.0.0
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

use function array_intersect_key;
use function explode;
use function str_contains;

/**
 * Validation manager class.
 *
 * The `ValidationManager` class provides a flexible and extensible way to perform data
 * validation. This class allows you to define validation rules and validate data against
 * those rules.
 *
 * @category  Omega
 * @package   Validation
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */
class Validation extends AbstractValidation
{
    /**
     * {@inheritdoc}
     */
    public function addRule(string $alias, RuleInterface $rule): static
    {
        $this->rules[$alias] = $rule;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $data, array $rules, string $sessionName = 'errors'): array
    {
        $errors = [];

        foreach ($rules as $field => $rulesForField) {
            if (!is_array($rulesForField)) {
                continue; // Skip if $rulesForField is not an array.
            }

            foreach ($rulesForField as $rule) {
                $name   = $rule;
                $params = [];

                if (is_string($rule) && str_contains($rule, ':')) {
                    [ $name, $params ] = explode(':', $rule, 2);
                    $params            = explode(',', $params);
                }

                if (!isset($this->rules[$name])) {
                    continue; // Skip if the rule processor is not found.
                }

                $processor = $this->rules[$name];

                if (!$processor->validate($data, $field, $params)) {
                    if (!isset($errors[$field])) {
                        $errors[$field] = [];
                    }

                    $errors[$field][] = $processor->getMessage($data, $field, $params);
                }
            }
        }

        if (count($errors)) {
            $exception = new ValidationException();
            $exception->setErrors($errors);
            $exception->setSessionName($sessionName);

            throw $exception;
        } else {
            $session = session();
            if ($session && is_object($session) && method_exists($session, 'forget')) {
                $session->forget($sessionName);
            }
        }

        return array_intersect_key($data, $rules);
    }
}
