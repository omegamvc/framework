<?php

/**
 * Part of Omega - Model Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Database;

/**
 * Relationship class.
 *
 * The `RelationShip` class represents relationship between models. This
 * class facilities relationship between models, providing a way to call
 * method on a ModelCollector instance and act as a callable object or
 * delegate method calls to the underlying ModelCollector.
 *
 * @category   Omega
 * @package    Database
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class Relationship
{
    /**
     * ModelCollector object.
     *
     * @var ModelCollector Holds an instance of ModelCollector.
     */
    public ModelCollector $collector;

    /**
     * Method name.
     *
     * @var string Holds the method name.
     */
    public string $method;

    /**
     * Relationship class constructor.
     *
     * @param ModelCollector $collector Holds an instance of ModelCollector.
     * @param string         $method    Holds the method name.
     * @return void
     */
    public function __construct(ModelCollector $collector, string $method)
    {
        $this->collector = $collector;
        $this->method    = $method;
    }

    /**
     * Call an object as a function.
     *
     * @param array<int|string, mixed> $parameters Holds an array of parameters.
     * @return mixed
     */
    public function __invoke(array $parameters = []): mixed
    {
        return $this->collector->__call($this->method, $parameters);
    }

    /**
     * Invoking inaccessible methods in an object context.
     *
     * @param string                   $method     Holds the method name.
     * @param array<int|string, mixed> $parameters Holds an array of parameters.
     * @return mixed
     */
    public function __call(string $method, array $parameters = []): mixed
    {
        return $this->collector->__call($method, $parameters);
    }
}
