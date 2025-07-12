<?php

/**
 * Part of Omega - Renderer Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\View\Engine;

use Exception;
use Omega\View\View;

use function debug_backtrace;
use function realpath;

use const DEBUG_BACKTRACE_IGNORE_ARGS;

/**
 * Abstract engine class.
 *
 * @category   Omega
 * @package    View
 * @subpackage Engine
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
abstract class AbstractEngine implements EngineInterface
{
    use HasManagerTrait;

    /**
     * Layout array.
     *
     * @var array<int|string, string> Holds an array of layouts.
     */
    protected array $layouts = [];

    /**
     * Extends the template.
     *
     * This method extends the current template with another layout template.
     *
     * @param string $template Holds the template name.
     * @return $this
     */
    protected function extends(string $template): static
    {
        $backtrace            = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file                 = isset($backtrace[0]['file']) ? realpath($backtrace[0]['file']) : null;
        $this->layouts[$file] = $template;

        return $this;
    }

    /**
     * Magic call.
     *
     * This method handles dynamic method calls, typically for macros.
     *
     * @param string $name      Holds the method name.
     * @param array  $values Holds the method params/values.
     * @return mixed
     * @throws Exception
     */
    public function __call(string $name, array $values): mixed
    {
        return $this->viewManager->useMacro($name, ...$values);
    }

    /**
     * {@inheritdoc}
     */
    abstract public function render(View $view): string;
}
