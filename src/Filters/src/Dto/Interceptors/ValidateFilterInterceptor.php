<?php

declare(strict_types=1);

namespace Spiral\Filters\Dto\Interceptors;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Dto\FilterBag;
use Spiral\Filters\Dto\FilterInterface;
use Spiral\Filters\Dto\HasFilterDefinition;
use Spiral\Filters\Dto\ShouldBeValidated;
use Spiral\Filters\ErrorMapper;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Validation\ValidationProviderInterface;

class ValidateFilterInterceptor implements CoreInterceptorInterface
{
    /** @param Container $container */
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    /**
     * @param array{filterBag: FilterBag} $parameters
     */
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): FilterInterface
    {
        $bag = $parameters['filterBag'];
        $filter = $core->callAction($controller, $action, $parameters);

        if ($filter instanceof HasFilterDefinition) {
            $this->validateFilter(
                $bag,
                $bag->errors ?? [],
                $parameters['context'] ?? null
            );
        }

        return $filter;
    }

    private function validateFilter(FilterBag $bag, array $errors, mixed $context): void
    {
        $definition = $bag->filter->filterDefinition();

        if ($definition instanceof ShouldBeValidated) {
            $errorMapper = new ErrorMapper($bag->schema);
            $validationProvider = $this->container->get(ValidationProviderInterface::class);

            $validator = $validationProvider
                ->getValidation($definition::class)
                ->validate($bag, $definition->validationRules(), $context);

            if (!$validator->isValid()) {
                throw new ValidationException(
                    $errorMapper->mapErrors(\array_merge($errors, $validator->getErrors())),
                    $context
                );
            }
        }
    }
}
