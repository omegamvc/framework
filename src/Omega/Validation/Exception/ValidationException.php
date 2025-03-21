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

namespace Omega\Validation\Exception;

use InvalidArgumentException;

/**
 * Validation exception class.
 *
 * The `ValidationException` is thrown when validation fails, and it provides a way
 * to store and retrieve validation errors.
 *
 * @category   Omega
 * @package    Validation
 * @subpackage Exception
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class ValidationException extends InvalidArgumentException
{
    /**
     * Errors array.
     *
     * @var array<string, array<string>> Holds an array of validation errors.
     */
    public array $errors = [];

    /**
     * Session name.
     *
     * @var string Holds the name of the session where validation errors should be stored.
     */
    public string $sessionName = 'errors';

    /**
     * Set the validation errors.
     *
     * @param array<string, array<string>> $errors Holds an array containing validation errors.
     *
     * @return $this
     */
    public function setErrors(array $errors): static
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * Get the validation errors.
     *
     * @return array<string, array<string>> An array of validation errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Set the session name for storing validation errors.
     *
     * @param string $sessionName Holds the name of the session where validation errors should be stored.
     *
     * @return $this
     */
    public function setSessionName(string $sessionName): static
    {
        $this->sessionName = $sessionName;

        return $this;
    }

    /**
     * Get the session name for storing validation errors.
     *
     * @return string Return the name of the session where validation errors are stored.
     */
    public function getSessionName(): string
    {
        return $this->sessionName;
    }
}
