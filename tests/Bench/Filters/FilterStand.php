<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Attributes\Factory;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\Container;
use Spiral\Core\Options;
use Spiral\Filter\InputScope;
use Spiral\Filters\Config\FiltersConfig;
use Spiral\Filters\InputInterface;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\FilterProvider;
use Spiral\Filters\Model\FilterProviderInterface;
use Spiral\Filters\Model\Interceptor\Core;
use Spiral\Filters\Model\Mapper\CasterRegistry;
use Spiral\Filters\Model\Mapper\CasterRegistryInterface;
use Spiral\Filters\Model\NestedInputLimits;
use Spiral\Http\Request\InputManager;

/**
 * A filter provider with the bare filter core: no validation and no interceptors, so a benchmark
 * counts only how the nested filters are built from the input.
 */
final class FilterStand
{
    private readonly Container $container;
    private readonly FilterProvider $provider;

    /**
     * @param array<string, mixed> $config Filters config, the limits are the defaults when omitted.
     */
    public function __construct(array $config = [])
    {
        $options = new Options();
        $options->checkScope = false;
        $this->container = new Container(options: $options);
        $this->container->bindSingleton(ReaderInterface::class, (new Factory())->create());
        $this->container->bindSingleton(CasterRegistryInterface::class, new CasterRegistry([]));
        $this->container->bindSingleton(NestedInputLimits::class, new NestedInputLimits(new FiltersConfig($config)));

        $this->provider = $this->container->make(FilterProvider::class, ['core' => new Core()]);
        $this->container->bind(FilterProviderInterface::class, $this->provider);
    }

    /**
     * Makes the input of a POST request with the given parsed body.
     */
    public function input(array $body): InputInterface
    {
        $this->container->bind(ServerRequestInterface::class, (new ServerRequest('POST', '/'))->withParsedBody($body));

        return new InputScope(new InputManager($this->container));
    }

    /**
     * @param class-string<FilterInterface> $class
     */
    public function create(string $class, InputInterface $input): FilterInterface
    {
        return $this->provider->createFilter($class, $input);
    }

    /**
     * Builds the filter once on a small input, so the counted call does not include autoloading
     * and reading the attributes of the filter classes.
     *
     * @param class-string<FilterInterface> $class
     */
    public function warmUp(string $class, array $body): void
    {
        $this->create($class, $this->input($body));
    }
}
