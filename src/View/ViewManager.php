<?php

/**
 * Part of Omega - View Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\View;

use Closure;
use Exception;
use Omega\View\Engine\EngineInterface;

use function array_push;
use function is_file;

/**
 * View manager class.
 *
 * The `ViewManager` class is responsible for managing views and rendering templates.
 *
 * @category  Omega
 * @package   View
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */
class ViewManager
{
    /**
     * View path.
     *
     * @var array<int, string> Holds an array of view paths where templates are searched for.
     */
    protected array $paths = [];

    /**
     * Engines engine.
     *
     * @var array<string, EngineInterface> Holds an array of engines associated with file extensions.
     */
    protected array $engines = [];

    /**
     * Macros.
     *
     * @var array<string, Closure> Holds an array of macro closures for extending functionality.
     */
    protected array $macros = [];

    /**
     * Add the view path.
     *
     * @param string $path Holds the view path to be added-
     * @return $this
     */
    public function addPath(string $path): static
    {
        array_push($this->paths, $path);

        return $this;
    }

    /**
     * Add a engine for a specific file extension.
     *
     * @param string          $extension Holds the file extension (e.g., 'php', 'html').
     * @param EngineInterface $engine    Holds an instance of the engine.
     * @return $this
     */
    public function addEngine(string $extension, EngineInterface $engine): static
    {
        $this->engines[$extension] = $engine;
        $this->engines[$extension]->setManager($this);

        return $this;
    }

    /**
     * Render a view template.
     *
     * @param string               $template Holds the template name (without extension).
     * @param array<string, mixed> $data     Holds an array of data to pass to the template.
     * @return View Return an instance of the rendered view.
     * @throws Exception if the specified template does not exist.
     */
    public function render(string $template, array $data = []): View
    {
        foreach ($this->engines as $extension => $engine) {
            foreach ($this->paths as $path) {
                $file = "$path/$template.$extension";

                if (is_file($file)) {
                    return new View($engine, realpath($file), $data);
                }
            }
        }

        throw new Exception(
            "Could not resolve '$template'"
        );
    }

    /**
     * Add a custom macro for extending functionality.
     *
     * @param string  $name    Holds the macro name.
     * @param Closure $closure Holds a closure that defines the macro's behavior.
     * @return $this
     */
    public function addMacro(string $name, Closure $closure): static
    {
        $this->macros[$name] = $closure;

        return $this;
    }

    /**
     * Use a defined macro.
     *
     * @param string $name      Holds the name of the macro to use.
     * @param mixed  ...$values Holds an additional values or parameters to pass to the macro.
     * @return mixed Returns the result of executing the specified macro.
     * @throws Exception if the specified macro is not defined.
     */
    public function useMacro(string $name, mixed ...$values): mixed
    {
        if (isset($this->macros[$name])) {
            $bound = $this->macros[$name]->bindTo($this);

            return $bound(...$values);
        }

        throw new Exception(
            "Macro isn't defined: '$name'"
        );
    }
}
