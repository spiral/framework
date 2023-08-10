<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Interceptor\Validation;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filters\ErrorMapper;
use Spiral\Filters\Exception\ValidationException;

final class ValidateFilterInterceptor implements CoreInterceptorInterface
{
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): array
    {
        \assert(\is_array($parameters['schema']));

        $errors = array_merge($core->callAction($controller, $action, $parameters), $parameters['errors'] ?? []);
        $errorMapper = new ErrorMapper($parameters['schema']);

        if ($errors !== []) {
            throw new ValidationException(
                $errorMapper->mapErrors($errors),
                $parameters['context'] ?? null
            );
        }

        return $errors;
    }
}
