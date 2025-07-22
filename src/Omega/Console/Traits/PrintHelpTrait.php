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

use Omega\Console\Style\Style;
use Omega\Text\Str;

use function array_keys;
use function implode;
use function strlen;

/**
 * Trait PrintHelpTrait
 *
 * Provides methods to render formatted help text for commands and options
 * using the console's style system.
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
trait PrintHelpTrait
{
    /**
     * Configuration for help text rendering.
     *
     * - 'margin-left': Left margin (number of spaces before the help line begins)
     * - 'column-1-min-length': Minimum width for the first column (command/option + args)
     *
     * @var array<string, string|int>
     */
    protected array $printHelp = [
        'margin-left'         => 12,
        'column-1-min-length' => 24,
    ];

    /**
     * Render a list of available commands with their arguments and descriptions,
     * aligned and styled using the Style object.
     *
     * @param Style $style The style object used to format console output.
     * @return Style The modified Style object, after printing the command help.
     */
    public function printCommands(Style $style): Style
    {
        $optionNames = array_keys($this->commandDescribes);

        $minLength = $this->printHelp['column-1-min-length'];
        foreach ($optionNames as $name) {
            $argumentsLength = 0;
            if (isset($this->commandRelation[$name])) {
                $arguments       = implode(' ', $this->commandRelation[$name]);
                $argumentsLength = strlen($arguments);
            }

            $length = strlen($name) + $argumentsLength;
            if ($length > $minLength) {
                $minLength = $length;
            }
        }

        foreach ($this->commandDescribes as $option => $describe) {
            $arguments = '';
            if (isset($this->commandRelation[$option])) {
                $arguments = implode(' ', $this->commandRelation[$option]);
                $arguments = ' ' . $arguments;
            }

            $style->repeat(' ', $this->printHelp['margin-left']);

            $style->push($option)->textGreen();
            $style->push($arguments)->textDim();

            $range = $minLength - (strlen($option) + strlen($arguments));
            $style->repeat(' ', $range + 8);

            $style->push($describe);
            $style->newLines();
        }

        return $style;
    }

    /**
     * Render a list of available options with their descriptions,
     * aligned and styled using the Style object.
     *
     * @param Style $style The style object used to format console output.
     * @return Style The modified Style object, after printing the option help.
     */
    public function printOptions(Style $style): Style
    {
        $optionNames = array_keys($this->optionDescribes);

        $minLength = $this->printHelp['column-1-min-length'];
        foreach ($optionNames as $name) {
            $length = strlen($name);
            if ($length > $minLength) {
                $minLength = $length;
            }
        }

        foreach ($this->optionDescribes as $option => $describe) {
            $style->repeat(' ', $this->printHelp['margin-left']);

            $option_name = Str::fillEnd($option, ' ', $minLength + 8);
            $style->push($option_name)->textDim();

            $style->push($describe);
            $style->newLines();
        }

        return $style;
    }
}
