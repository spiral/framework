<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\InputInterface;

/**
 * Sets property value with route attribute with given key.
 *
 * Every route writes matched parameters into ServerRequestInterface attribute matches, is it possible to access route
 * values inside your filter using attribute:matches.{name} notation.
 *
 * $router->setRoute(
 *      'sample',
 *      new Route('/action/<id>.html', new Controller(HomeController::class))
 * );
 */
#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class Route extends AbstractInput
{
    /**
     * @param non-empty-string|null $key
     */
    public function __construct(
        public readonly ?string $key = null,
    ) {
    }

    /**
     * @see \Spiral\Http\Request\InputManager::attribute() from {@link https://github.com/spiral/http}
     */
    public function getValue(InputInterface $input, \ReflectionProperty $property): mixed
    {
        return $input->getValue('attribute', 'matches.' . $this->getKey($property));
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'attribute:matches.' . $this->getKey($property);
    }
}
