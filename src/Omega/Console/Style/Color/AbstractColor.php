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

namespace Omega\Console\Style\Color;

/**
 * Abstract base class implementing common logic for color objects
 * using a set of rules.
 *
 * @category   Omega
 * @package    Console
 * @subpackage Style\Color
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
abstract class AbstractColor implements RuleInterface
{
    /** @var array<int, int> Current set of rules for this color */
    private array $rules = [];

    /** @var array<int, int> Current set of rules for this color */
    public array $rule {
        get {
            return $this->rules;
        }
        set(array $value) {
            $this->rules = $value;
        }
    }

    /**
     * Abstract coloro class constructor.
     *
     * @param array<int, int>|null $rule Optional initial set of rules
     */
    public function __construct(?array $rule = [])
    {
        if ($rule !== null) {
            $this->setRule($rule);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRule(): array
    {
        return $this->rules;
    }

    /**
     * {@inheritdoc}
     */
    public function setRule(?array $rule = []): void
    {
        if ($rule !== null) {
            $this->rules = $rule;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasRule(int $rule): bool
    {
        return in_array($rule, $this->rules, true);
    }

    /**
     * {@inheritdoc}
     */
    public function clearRule(): void
    {
        $this->rules = [];
    }

    /**
     * {@inheritdoc}
     */
    public function rawRule(): string
    {
        return implode(';', $this->rules);
    }
}
