<?php

declare(strict_types=1);

namespace Spiral\Filters\Interceptors;

use Spiral\Core\Container;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Filter;
use Spiral\Filters\ShouldBeValidated;
use Spiral\Validation\ValidationInterface;

class ValidateFilterInterceptor implements CoreInterceptorInterface
{
    public function __construct(
        private readonly Container $container = new Container()
    ) {
    }

    public function process(string $name, string $action, array $parameters, CoreInterface $core): mixed
    {
        /** @var Filter $filter */
        $filter = $core->callAction($name, $action, $parameters);

        if ($filter instanceof ShouldBeValidated) {
            $validation = $this->container->get(ValidationInterface::class);
            $filter->withValidation($validation);
            $validator = $filter->validate();

            if (!$validator->isValid()) {
                $filter->failedValidation($validator);
            }
        }

        return $filter;
    }
}
