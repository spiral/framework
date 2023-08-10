<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Interceptor\Validation;

use Psr\Container\ContainerInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Filters\Model\ShouldBeValidated;
use Spiral\Validation\ValidationProviderInterface;

final class Core implements CoreInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public function callAction(string $controller, string $action, array $parameters = []): array
    {
        \assert($parameters['filter'] instanceof FilterInterface);
        \assert(\is_array($parameters['data']));
        \assert(\is_array($parameters['schema']));

        return $this->validateFilter($parameters['filter'], $parameters['data'], $parameters['context'] ?? null);
    }

    private function validateFilter(FilterInterface $filter, array $data, mixed $context = null): array
    {
        if ($filter instanceof HasFilterDefinition) {
            $definition = $filter->filterDefinition();
            if ($definition instanceof ShouldBeValidated) {
                /** @var ValidationProviderInterface $validationProvider */
                $validationProvider = $this->container->get(ValidationProviderInterface::class);

                $validator = $validationProvider
                    ->getValidation($definition::class)
                    ->validate($data, $definition->validationRules(), $context);

                return !$validator->isValid() ? $validator->getErrors() : [];
            }
        }

        return [];
    }
}
