<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Interceptor;

use Psr\Container\ContainerInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Model\FilterBag;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Filters\Model\ShouldBeValidated;
use Spiral\Filters\ErrorMapper;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Validation\ValidationProviderInterface;

/**
 * @psalm-type TParameters = array{filterBag: FilterBag}
 */
final class ValidateFilterInterceptor implements CoreInterceptorInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    /**
     * @param-assert TParameters $parameters
     */
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): FilterInterface
    {
        $bag = $parameters['filterBag'];
        $filter = $core->callAction($controller, $action, $parameters);

        if ($filter instanceof HasFilterDefinition) {
            $this->validateFilter(
                $filter->filterDefinition(),
                $bag,
                $bag->errors ?? [],
                $parameters['context'] ?? null
            );
        }

        if (($bag->errors ?? []) !== []) {
            $errorMapper = new ErrorMapper($bag->schema);
            throw new ValidationException($errorMapper->mapErrors($bag->errors), $parameters['context'] ?? null);
        }

        return $filter;
    }

    private function validateFilter(
        FilterDefinitionInterface $definition,
        FilterBag $bag,
        array $errors,
        mixed $context
    ): void {
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
            } elseif ($errors !== []) {
                throw new ValidationException($errorMapper->mapErrors($errors), $context);
            }
        }
    }
}
