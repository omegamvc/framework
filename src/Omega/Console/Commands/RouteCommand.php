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

namespace Omega\Console\Commands;

use Omega\Console\Command;
use Omega\Console\Style\Alert;
use Omega\Console\Style\Style;
use Omega\Console\Traits\PrintHelpTrait;
use Omega\Router\Router;
use Omega\Text\Str;

use function count;
use function is_array;
use function Omega\Console\style;
use function strtoupper;

/**
 * Displays the list of registered application routes.
 *
 * This command prints all available routes in a formatted table,
 * including HTTP methods, route names, and expressions (URIs).
 *
 * ## Usage:
 *   php omega route:list
 *
 * ## Example Output:
 *   GET     home................................. /                      ✔
 *   POST    login................................ /login                 ✔
 *   GET|POST user.profile........................ /user/{id}/profile     ✔
 *
 * @category   Omega
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class RouteCommand extends Command
{
    // use CommandTrait;
    use PrintHelpTrait;

    /**
     * The route:list command registration metadata.
     *
     * Each command includes the command name (cmd), the callable function (fn),
     * and optionally other options or default values.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'cmd' => 'route:list',
            'fn'  => [RouteCommand::class, 'main'],
        ],
    ];

    /**
     * Returns help information for the command.
     *
     * This method provides descriptive help text used by the command help system.
     * Includes the list of commands and any associated options or relationships.
     *
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'route:list' => 'Get route list information',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    /**
     * Executes the route:list command.
     *
     * Iterates through all registered routes using the Router class,
     * applies colored styles based on HTTP method, and prints each route
     * with its name and expression in a formatted layout.
     *
     * @return int  Returns 0 on success.
     */
    public function main(): int
    {
        $print = new Style();
        $print->tap(Alert::render()->ok('route list'));
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach (Router::getRoutes() as $key => $route) {
            $method = $this->methodToStyle($route['method']);
            $name   = style($route['name'])->textWhite();
            $length = $method->length() + $name->length();

            $print
              ->tap($method)
              ->push(' ')
              ->tap($name)
              ->repeat('.', 80 - $length)->textDim()
              ->push(' ')
              ->push(Str::limit($route['expression'], 30))
              ->newLines()
            ;
        }
        $print->out();

        return 0;
    }

    /**
     * Converts one or more HTTP methods to styled text.
     *
     * If multiple methods are passed (e.g., ['GET', 'POST']),
     * the output will be joined with a dimmed pipe symbol.
     *
     * @param string|string[] $methods  The HTTP method(s) to style.
     * @return Style  The formatted style representation of the method(s).
     */
    private function methodToStyle(string|array $methods): Style
    {
        if (is_array($methods)) {
            $group  = new Style();
            $length = count($methods);
            for ($i = 0; $i < $length; $i++) {
                $group->tap($this->coloringMethod($methods[$i]));
                if ($i < $length - 1) {
                    $group->push('|')->textDim();
                }
            }

            return $group;
        }

        return $this->coloringMethod($methods);
    }

    /**
     * Applies a color style to a specific HTTP method.
     *
     * Styling rules:
     * - GET: Blue
     * - POST, PUT: Yellow
     * - DELETE: Red
     * - Other methods: Dim
     *
     * @param string $method  The HTTP method name.
     * @return Style  The colored style instance.
     */
    private function coloringMethod(string $method): Style
    {
        $method = strtoupper($method);

        if ($method === 'GET') {
            return (new Style($method))->textBlue();
        }

        if ($method === 'POST' || $method === 'PUT') {
            return (new Style($method))->textYellow();
        }

        if ($method === 'DELETE') {
            return (new Style($method))->textRed();
        }

        return (new Style($method))->textDim();
    }
}
