<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Core;

use Spiral\Core\Exception\InterceptorException;

/**
 * Provides the ability to modify the call to the domain core on it's way to the action.
 */
final class InterceptorPipeline implements CoreInterface
{
    /** @var CoreInterface */
    private $core;

    /** @var CoreInterceptorInterface[] */
    private $interceptors = [];

    /** @var int */
    private $position = 0;

    public function addInterceptor(CoreInterceptorInterface $interceptor): void
    {
        $this->interceptors[] = $interceptor;
    }

    public function withCore(CoreInterface $core): self
    {
        $pipeline = clone $this;
        $pipeline->core = $core;

        return $pipeline;
    }

    /**
     * @param string|null $action
     * @return mixed
     * @throws \Throwable
     */
    public function callAction(string $controller, string $action, array $parameters = [])
    {
        if ($this->core === null) {
            throw new InterceptorException('Unable to invoke pipeline without assigned core');
        }

        $position = $this->position++;
        if (isset($this->interceptors[$position])) {
            return $this->interceptors[$position]->process($controller, $action, $parameters, $this);
        }

        return $this->core->callAction($controller, $action, $parameters);
    }
}
