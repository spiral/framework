<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Core;

/**
 * The domain core with a set of domain action interceptors (business logic middleware).
 */
final class InterceptableCore implements CoreInterface
{
    /** @var InterceptorPipeline */
    private $pipeline;

    /** @var CoreInterface */
    private $core;

    /**
     * @param CoreInterface $core
     */
    public function __construct(CoreInterface $core)
    {
        $this->pipeline = new InterceptorPipeline();
        $this->core = $core;
    }

    /**
     * @param CoreInterceptorInterface $interceptor
     */
    public function addInterceptor(CoreInterceptorInterface $interceptor): void
    {
        $this->pipeline->addInterceptor($interceptor);
    }

    /**
     * @inheritDoc
     */
    public function callAction(string $controller, string $action, array $parameters = [])
    {
        return $this->pipeline->withCore($this->core)->callAction($controller, $action, $parameters);
    }
}
