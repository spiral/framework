<?php

declare(strict_types=1);

namespace Spiral\Events;

use Spiral\Attributes\ReaderInterface;
use Spiral\Events\Attribute\Listener;
use Spiral\Tokenizer\ScopedClassesInterface;

final class ListenerLocator implements ListenerLocatorInterface
{
    public const SCOPE_NAME = 'event-listeners';

    public function __construct(
        private readonly ScopedClassesInterface $locator,
        private readonly ReaderInterface $reader
    ) {
    }

    public function findListeners(): \Generator
    {
        foreach ($this->locator->getScopedClasses(self::SCOPE_NAME) as $class) {
            $attrs = $this->reader->getClassMetadata($class, Listener::class);

            foreach ($attrs as $attr) {
                yield $class->getName() => $attr;
            }

            foreach ($class->getMethods() as $method) {
                $attrs = $this->reader->getFunctionMetadata($method, Listener::class);

                foreach ($attrs as $attr) {
                    $attr->method = $method->getName();

                    yield $class->getName() => $attr;
                }
            }
        }
    }
}
