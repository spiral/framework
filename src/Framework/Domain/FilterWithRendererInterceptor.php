<?php

declare(strict_types=1);

namespace Spiral\Domain;

use Psr\Container\ContainerInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Domain\Exception\InvalidFilterException;
use Spiral\Filters\FilterInterface;
use Spiral\Filters\RenderErrors;
use Spiral\Filters\RenderWith;

/**
 * Automatically validate all Filters and return array error in case of failure.
 */
class FilterWithRendererInterceptor extends FilterInterceptor
{
    /** @var array<class-string<FilterInterface>, RenderErrors> @internal */
    private array $renderersCache = [];

    /** @internal */
    private ReaderInterface $reader;

    /** @internal */
    private RenderErrors $renderErrors;

    public function __construct(
        ContainerInterface $container,
        ReaderInterface $reader,
        ?RenderErrors $renderErrors = null,
        int $strategy = self::STRATEGY_JSON_RESPONSE
    ) {
        parent::__construct($container, $strategy);

        $this->reader = $reader;
        $this->renderErrors = $renderErrors ?: new DefaultFilterErrorsRenderer($strategy);
    }

    /**
     * @throws InvalidFilterException
     */
    protected function renderInvalid(FilterInterface $filter)
    {
        return ($this->renderersCache[get_class($filter)] ?? $this->renderErrors)->render($filter);
    }

    protected function buildCache(\ReflectionParameter $parameter, \ReflectionClass $class, string $key): void
    {
        parent::buildCache($parameter, $class, $key);

        if (null !== ($renderWith = $this->reader->firstClassMetadata($class, RenderWith::class))) {
            $this->renderersCache[$class->getName()] = $this->container->get($renderWith->getRenderer());
        }
    }
}
