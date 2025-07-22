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

namespace Omega\Console\Style\Color;

use function implode;

/**
 * Base class representing a color rule for terminal styling.
 *
 * This abstract class defines a terminal rule composed of a sequence of numeric codes
 * that correspond to terminal formatting instructions (e.g., foreground or background color codes).
 * It implements the RuleInterface and provides methods to retrieve the rule as an array
 * or as a raw string suitable for ANSI escape sequences.
 *
 * @category   Omega
 * @package    Console
 * @subpackage Style\Color
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
abstract class AbstractColor implements RuleInterface
{
    /**
     * Terminal formatting rule.
     *
     * Each integer in the array corresponds to a specific terminal control code.
     *
     * @var array<int, int>
     */
    protected array $rule;

    /**
     * Create a new color rule.
     *
     * @param array<int, int> $rule An array of integer values representing the ANSI color codes.
     */
    public function __construct(array $rule)
    {
        $this->rule = $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function get(): array
    {
        return $this->rule;
    }

    /**
     * {@inheritdoc}
     */
    public function raw(): string
    {
        return implode(';', $this->rule);
    }
}
