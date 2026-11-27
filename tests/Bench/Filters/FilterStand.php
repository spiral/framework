<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Attributes\Factory;
use Spiral\Attributes\ReaderInterface;
use Spiral\Core\CompatiblePipelineBuilder;
use Spiral\Core\Container;
use Spiral\Core\Options;
use Spiral\Filter\InputScope;
use Spiral\Filters\Config\FiltersConfig;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Filters\InputInterface;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\FilterProvider;
use Spiral\Filters\Model\FilterProviderInterface;
use Spiral\Filters\Model\Interceptor\Core;
use Spiral\Filters\Model\Interceptor\ValidateFilterInterceptor;
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
    /**
     * A limit of nested filters far above the default, so the data sets can grow past the default and
     * a benchmark measures how the cost grows, not where the default limit stops it.
     */
    private const LOOSE_CONFIG = ['maxNestedFilters' => 1_000_000];

    private readonly Container $container;
    private readonly FilterProvider $provider;

    /**
     * @param array<string, mixed>|null $config Filters config; omitted limits take their defaults. Null
     *        takes {@see self::LOOSE_CONFIG}.
     * @param bool $validate Puts {@see ValidateFilterInterceptor} in front of the core, as the framework
     *        does, so a filter with errors throws and its errors reach the parent filter.
     */
    public function __construct(?array $config = null, bool $validate = false)
    {
        $config ??= self::LOOSE_CONFIG;
        $options = new Options();
        $options->checkScope = false;
        $this->container = new Container(options: $options);
        $this->container->bindSingleton(ReaderInterface::class, (new Factory())->create());
        $this->container->bindSingleton(CasterRegistryInterface::class, new CasterRegistry([]));
        $this->container->bindSingleton(NestedInputLimits::class, new NestedInputLimits(new FiltersConfig($config)));

        $core = $validate
            ? (new CompatiblePipelineBuilder())
                ->withInterceptors(new ValidateFilterInterceptor($this->container))
                ->build(new Core())
            : new Core();

        $this->provider = $this->container->make(FilterProvider::class, ['core' => $core]);
        $this->container->bind(FilterProviderInterface::class, $this->provider);
    }

    /**
     * Makes the input of a POST request with the given parsed body and query.
     */
    public function input(array $body, array $query = []): InputInterface
    {
        $this->container->bind(
            ServerRequestInterface::class,
            (new ServerRequest('POST', '/'))->withParsedBody($body)->withQueryParams($query),
        );

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
    public function warmUp(string $class, array $body, array $query = []): void
    {
        try {
            $this->create($class, $this->input($body, $query));
        } catch (ValidationException) {
            // A warm-up input of a benchmark of errors fails validation as well.
        }
    }
}
