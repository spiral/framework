<?php

declare(strict_types=1);

namespace Spiral\Domain;

use Psr\Container\ContainerInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Domain\Exception\InvalidFilterException;
use Spiral\Filters\FilterInterface;
use Spiral\Filters\RenderErrorsInterface;
use Spiral\Filters\RenderWith;

/**
 * Automatically validate all Filters and return array error in case of failure.
 */
class FilterWithRendererInterceptor extends FilterInterceptor
{
    /**
     * @var array<class-string<FilterInterface>, RenderErrorsInterface>
     * @internal
     */
    private array $renderersCache = [];

    /** @internal */
    private ReaderInterface $reader;

    public function __construct(
        ContainerInterface $container,
        ReaderInterface $reader,
        ?RenderErrorsInterface $renderErrors = null,
        int $strategy = self::STRATEGY_JSON_RESPONSE
    ) {
        parent::__construct($container, $strategy, $renderErrors);

        $this->reader = $reader;
    }

    /**
     * @throws InvalidFilterException
     */
    protected function renderInvalid(FilterInterface $filter)
    {
        $filterClass = get_class($filter);

        if (isset($this->renderersCache[$filterClass])) {
            return $this->renderersCache[$filterClass]->render($filter);
        }

        return parent::renderInvalid($filter);
    }

    protected function buildCache(\ReflectionParameter $parameter, \ReflectionClass $class, string $key): void
    {
        parent::buildCache($parameter, $class, $key);

        if (null !== ($renderWith = $this->reader->firstClassMetadata($class, RenderWith::class))) {
            $this->renderersCache[$class->getName()] = $this->container->get($renderWith->getRenderer());
        }
    }
}
