<?php

declare(strict_types=1);

namespace Omega\Console;

use Exception;
use Omega\Console\Style\Style;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_pop;
use function array_values;
use function chr;
use function fwrite;
use function join;
use function readline_callback_handler_install;
use function stream_get_contents;
use const STDIN;
use const STDOUT;

/**
 * Add customize terminal style by adding traits:
 * - TraitCommand (optional).
 *
 * @property string $_ Get argument name
 */
class Prompt
{
    /** @var string|Style */
    private string|Style $title;

    /** @var array<string, callable> */
    private array $options;

    /** @var string  */
    private string $default;

    /** @var string[]|Style[] */
    private array $selection;

    /**
     * @param string|Style $title
     * @param array<string, callable> $options
     * @param string $default
     */
    public function __construct(Style|string $title, array $options = [], string $default = '')
    {
        $this->title     = $title;
        $this->options   = array_merge(['' => fn () => false], $options);
        $this->default   = $default;
        $this->selection = array_keys($options);
    }

    /**
     * @return string
     * @throws Exception
     */
    private function getInput(): string
    {
        $input = fgets(STDIN);

        if ($input === false) {
            throw new Exception('Cant read input');
        }

        return trim($input);
    }

    /**
     * @param string[]|Style[] $selection
     * @return self
     */
    public function selection(array $selection): self
    {
        $this->selection = $selection;

        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function option(): mixed
    {
        $style = new Style();
        $style->push((string)$this->title)->push(' ');
        foreach ($this->selection as $option) {
            if ($option instanceof Style) {
                $style->tap($option);
            } else {
                $style->push("$option ");
            }
        }

        $style->out();
        $input = $this->getInput();
        if (array_key_exists($input, $this->options)) {
            return ($this->options[$input])();
        }

        return ($this->options[$this->default])();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function select(): mixed
    {
        $style = new Style();
        $style->push($this->title);
        $i = 1;
        foreach ($this->selection as $option) {
            if ($option instanceof Style) {
                $style->tap($option);
            } else {
                /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
                $style->newLines()->push("[{$i}] {$option}");
            }
            $i++;
        }

        $style->out();
        $input  = $this->getInput();
        $select = array_values($this->options);

        if (array_key_exists($input, $select)) {
            return ($select[$input])();
        }

        return ($this->options[$this->default])();
    }

    /**
     * @param callable $callable
     * @return mixed
     * @throws Exception
     */
    public function text(callable $callable): mixed
    {
        new Style($this->title)->out();

        return ($callable)($this->getInput());
    }

    /**
     * @param callable $callable
     * @param string $mask
     * @return mixed
     */
    public function password(callable $callable, string $mask = ''): mixed
    {
        new Style($this->title)->out();

        $userLine = [];
        readline_callback_handler_install('', function () {});
        while (true) {
            $keystroke = stream_get_contents(STDIN, 1);

            switch (ord($keystroke)) {
                case 10:
                    break 2;

                case 127:
                    array_pop($userLine);
                    fwrite(STDOUT, chr(8));
                    fwrite(STDOUT, "\033[0K");
                    break;

                default:
                    $userLine[] = $keystroke;
                    fwrite(STDOUT, $mask);
                    break;
            }
        }

        return ($callable)(join($userLine));
    }

    /**
     * @param callable $callable
     * @return mixed
     */
    public function anyKey(callable $callable): mixed
    {
        $prompt = (string) $this->title;
        readline_callback_handler_install($prompt, function () {});
        $keystroke = stream_get_contents(STDIN, 1);

        return ($callable)($keystroke);
    }
}
