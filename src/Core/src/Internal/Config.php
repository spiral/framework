<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

class Config
{
    public string $state = State::class;
    public string $resolver = Resolver::class;
    public string $factory = Factory::class;
    public string $container = Container::class;
    public string $binder = Binder::class;
    public string $invoker = Invoker::class;
}
