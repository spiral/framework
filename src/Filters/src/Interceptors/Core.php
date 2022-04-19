<?php

declare(strict_types=1);

namespace Spiral\Filters\Interceptors;

use Spiral\Core\CoreInterface;
use Spiral\Filters\FilterProviderInterface;
use Spiral\Filters\InputInterface;

final class Core implements CoreInterface
{
    public function __construct(
        private readonly FilterProviderInterface $provider,
        private readonly InputInterface $input,
    ) {
    }

    public function callAction(string $name, string $action, array $parameters = []): mixed
    {
        return $this->provider->createFilter($name, $this->input);
    }
}
