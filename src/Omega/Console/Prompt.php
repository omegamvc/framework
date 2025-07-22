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

namespace Omega\Console;

use Exception;
use Omega\Console\Style\Style;

use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_pop;
use function array_values;
use function fgets;
use function fwrite;
use function join;
use function ord;
use function readline_callback_handler_install;
use function stream_get_contents;
use function trim;

/**
 * Class Prompt
 *
 * Provides a styled CLI prompt for selecting options, entering text, and handling user input,
 * including hidden input (passwords) and single key detection.
 *
 * Add customize terminal style by adding trits:
 * - TraitCommand (optional).
 *
 * @category  Omega
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 *
 * @property string $_ Get argument name
 */
class Prompt
{
    /** @var string|Style The title displayed before the prompt. It can be a plain string or a styled output. */
    private string|Style $title;

    /** @var array<string, callable> A list of selectable options mapped to their respective callables. */
    private array $options;

    /** @var string The default option key to fall back on if user input does not match any. */
    private string $default;

    /**
     * A list of options to be displayed during selection. These can be plain strings or styled items.
     *
     * @var string[]|Style[]
     */
    private array $selection;

    /**
     * Prompt constructor.
     *
     * @param string|Style            $title    The title to display.
     * @param array<string, callable> $options  The available options mapped to callbacks.
     * @param string                  $default  The default option key.
     * @return void
     */
    public function __construct(Style|string $title, array $options = [], string $default = '')
    {
        $this->title     = $title;
        $this->options   = array_merge(['' => fn () => false], $options);
        $this->default   = $default;
        $this->selection = array_keys($options);
    }

    /**
     * Read input from STDIN and trim it.
     *
     * @return string
     * @throws Exception If reading input fails.
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
     * Set a custom selection to be displayed during option prompt.
     *
     * @param string[]|Style[] $selection
     * @return $this
     */
    public function selection(array $selection): self
    {
        $this->selection = $selection;

        return $this;
    }

    /**
     * Display the available options and execute the callable corresponding to the selected input.
     *
     * @return mixed
     * @throws Exception If input reading fails.
     * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
     */
    public function option(): mixed
    {
        $style = new Style();
        $style->push((string)$this->title)->push(' ');
        foreach ($this->selection as $option) {
            if ($option instanceof Style) {
                $style->tap($option);
            } else {
                $style->push("{$option} ");
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
     * Display a numbered list of options and execute the corresponding callback by number index.
     *
     * @return mixed
     * @throws Exception If input reading fails.
     * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
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
     * Display a message and pass user input to the provided callback.
     *
     * @param callable $callable The function to execute with the user input.
     * @return mixed
     * @throws Exception If input reading fails.
     */
    public function text(callable $callable): mixed
    {
        (new Style($this->title))->out();

        return ($callable)($this->getInput());
    }

    /**
     * Accept password input from the user, masking it as typed, and execute the provided callback with the result.
     *
     * @param callable $callable The function to execute with the user input.
     * @param string $mask       The character used to mask each typed character.
     * @return mixed
     */
    public function password(callable $callable, string $mask = ''): mixed
    {
        (new Style($this->title))->out();

        $userLine = [];
        readline_callback_handler_install('', function () {
        });
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
     * Wait for any key press and execute the provided callback with the key pressed.
     *
     * @param callable $callable The function to execute with the key character.
     * @return mixed
     */
    public function anyKey(callable $callable): mixed
    {
        $prompt = (string) $this->title;
        readline_callback_handler_install($prompt, function () {
        });
        $keystroke = stream_get_contents(STDIN, 1);

        return ($callable)($keystroke);
    }
}
