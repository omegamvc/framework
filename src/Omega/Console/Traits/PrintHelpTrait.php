<?php

declare(strict_types=1);

namespace Omega\Console\Traits;

use Omega\Console\Style\Style;
use Omega\Text\Str;

trait PrintHelpTrait
{
    /**
     * Print helper style option.
     *
     * @var array<string, string|int>
     */
    protected $printHelp = [
        'margin-left'         => 12,
        'column-1-min-length' => 24,
    ];

    /**
     * Print argument describe using style console.
     *
     * @return Style
     */
    public function printCommands(Style $style)
    {
        $option_names =  array_keys($this->commandDescribes);

        $min_length = $this->printHelp['column-1-min-length'];
        foreach ($option_names as $name) {
            $arguments_lenght = 0;
            if (isset($this->commandRelation[$name])) {
                $arguments        = implode(' ', $this->commandRelation[$name]);
                $arguments_lenght = \strlen($arguments);
            }

            $lenght = \strlen($name) + $arguments_lenght;
            if ($lenght > $min_length) {
                $min_length = $lenght;
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

            $range = $min_length - (\strlen($option) + \strlen($arguments));
            $style->repeat(' ', $range + 8);

            $style->push($describe);
            $style->newLines();
        }

        return $style;
    }

    /**
     * Print option describe using style console.
     *
     * @return Style
     */
    public function printOptions(Style $style)
    {
        $option_names =  array_keys($this->optionDescribes);

        $min_length = $this->printHelp['column-1-min-length'];
        foreach ($option_names as $name) {
            $lenght = \strlen($name);
            if ($lenght > $min_length) {
                $min_length = $lenght;
            }
        }

        foreach ($this->optionDescribes as $option => $describe) {
            $style->repeat(' ', $this->printHelp['margin-left']);

            $option_name = Str::fillEnd($option, ' ', $min_length + 8);
            $style->push($option_name)->textDim();

            $style->push($describe);
            $style->newLines();
        }

        return $style;
    }
}
