<?php

declare(strict_types=1);

namespace Spiral\Filters\Interceptors;

use Spiral\Core\Container;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filters\ErrorMapper;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Filters\FilterBag;
use Spiral\Filters\FilterInterface;
use Spiral\Filters\HasFilterDefinition;
use Spiral\Filters\ShouldBeValidated;
use Spiral\Validation\ValidationProvider;

class ValidateFilterInterceptor implements CoreInterceptorInterface
{
    public function __construct(
        private readonly Container $container = new Container()
    ) {
    }

    public function process(string $name, string $action, array $parameters, CoreInterface $core): FilterInterface
    {
        /** @var FilterBag $bag */
        $bag = $parameters['filterBag'];

        if ($bag->filter instanceof HasFilterDefinition) {
            $this->validateFilter(
                $bag,
                $parameters['errors'] ?? [],
                $parameters['context'] ?? null
            );
        }

        return $core->callAction($name, $action, $parameters);
    }

    private function validateFilter(FilterBag $bag, array $errors, mixed $context): void
    {
        $definition = $bag->filter->filterDefinition();

        if ($definition instanceof ShouldBeValidated) {
            $errorMapper = new ErrorMapper($bag->schema);
            $manager = $this->container->get(ValidationProvider::class);

            $validator = $manager->getValidation($definition::class)
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
