<?php

/**
 * Part of Omega - Renderer Package.
 *
 * @see       https://omegamvc.github.io
 *
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\View\Engine;

/*
 * @use
 */
use function debug_backtrace;
use function realpath;
use Omega\View\View;
use Exception;

/**
 * Abstract engine class.
 *
 * @category    Omega
 * @package     View
 * @subpackage  Engine
 *
 * @see        https://omegamvc.github.io1
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
abstract class AbstractEngine implements EngineInterface
{
    use HasManagerTrait;

    /**
     * Layout array.
     *
     * @var array Holds an array of layouts.
     */
    protected array $layouts = [];

    /**
     * Extends the template.
     *
     * This method extends the current template with another layout template.
     *
     * @param string $template Holds the template name.
     *
     * @return $this
     */
    protected function extends(string $template): static
    {
        $backtrace                                      = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $this->layouts[realpath($backtrace[0]['file'])] = $template;

        return $this;
    }

    /**
     * Magic call.
     *
     * This method handles dynamic method calls, typically for macros.
     *
     * @param string $name      Holds the method name.
     * @param mixed  ...$values Holds the method params/values.
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function __call(string $name, mixed $values): mixed
    {
        return $this->viewManager->useMacro($name, ...$values);
    }

    /**
     * {@inheritdoc}
     *
     * This method is responsible for rendering a View object and processing its contents.
     *
     * @param View $view Holds an instance of View.
     *
     * @return string Return the view.
     */
    abstract public function render(View $view): string;
}
