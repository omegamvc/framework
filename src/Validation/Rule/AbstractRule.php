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
 * Abstract rule class.
 *
 * The `AbstractRule` class provides a foundation for implementing custom validation
 * rules in Omega. It implements the RuleInterface, which defines the contract for
 * all validation rules.
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
abstract class AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    abstract public function validate(array $data, string $field, array $params): string|bool;

    /**
     * {@inheritdoc}
     */
    abstract public function getMessage(array $data, string $field, array $params): string;
}
