<?php

declare(strict_types=1);

namespace Omega\Console\Traits;

use Omega\Console\Style\Style;
use Omega\Text\Str;
use function array_keys;
use function implode;
use function strlen;

trait PrintHelpTrait
{
    /** @var array<string, string|int> Print helper style option. */
    protected array $printHelp = [
        'margin-left'         => 2,
        'column-1-min-length' => 24,
    ];

    /**
     * Print argument describe using style console.
     *
     * @param Style $style
     * @return Style
     */
    public function printCommands(Style $style): Style
    {
        $optionNames =  array_keys($this->commandDescribes);

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
     * Print option describe using style console.
     *
     * @param Style $style
     * @return Style
     */
    public function printOptions(Style $style): Style
    {
        $optionNames =  array_keys($this->optionDescribes);

        $minLength = $this->printHelp['column-1-min-length'];
        foreach ($optionNames as $name) {
            $length = strlen($name);
            if ($length > $minLength) {
                $minLength = $length;
            }
        }

        foreach ($this->optionDescribes as $option => $describe) {
            $style->repeat(' ', $this->printHelp['margin-left']);

            $optionName = Str::fillEnd($option, ' ', $minLength + 8);
            $style->push($optionName)->textDim();

            $style->push($describe);
            $style->newLines();
        }

        return $style;
    }
}
