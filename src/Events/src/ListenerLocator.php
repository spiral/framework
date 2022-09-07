<?php

declare(strict_types=1);

namespace Spiral\Events;

use Spiral\Attributes\ReaderInterface;
use Spiral\Events\Attribute\Listener;
use Spiral\Tokenizer\ScopedClassesInterface;

final class ListenerLocator implements ListenerLocatorInterface
{
    public function __construct(
        private readonly ScopedClassesInterface $locator,
        private readonly ReaderInterface $reader
    ) {
    }

    /**
     * @return \Generator<class-string, Listener>
     */
    public function findListeners(): \Generator
    {
        foreach ($this->locator->getScopedClasses('listeners') as $class) {
            $attr = $this->reader->firstClassMetadata($class, Listener::class);

            if ($attr !== null) {
                yield $class->getName() => $attr;
            }

            foreach ($class->getMethods() as $method) {
                $attr = $this->reader->firstFunctionMetadata($method, Listener::class);

                if ($attr !== null) {
                    $attr->method = $method->getName();

                    yield $class->getName() => $attr;
                }
            }
        }
    }
}
