<?php

declare(strict_types=1);

namespace Omega\Container\Attribute;

use Attribute;
use JsonException;
use Omega\Container\Definition\Exceptions\InvalidAttributeException;

use function is_array;
use function is_string;
use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * #[Inject] attribute.
 *
 * Marks a property or method as an injection point
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
class Inject
{
    /**
     * Entry name.
     */
    public ?string $name = null { // phpcs:ignore
        get {
            return $this->name; // phpcs:ignore
        }
    }

    /**
     * Parameters, indexed by the parameter number (index) or name.
     *
     * Used if the attribute is set on a method
     */
    private array $parameters = [];

    /**
     * @throws InvalidAttributeException
     * @throws JsonException
     */
    public function __construct(string|array|null $name = null)
    {
        // #[Inject('foo')] or #[Inject(name: 'foo')]
        if (is_string($name)) {
            $this->name = $name;
        }

        // #[Inject([...])] on a method
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                if (!is_string($value)) {
                    throw new InvalidAttributeException(sprintf(
                        "#[Inject(['param' => 'value'])] expects \"value\" to be a string, %s given.",
                        json_encode($value, JSON_THROW_ON_ERROR)
                    ));
                }

                $this->parameters[$key] = $value;
            }
        }
    }

    /**
     * @return array Parameters, indexed by the parameter number (index) or name
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
