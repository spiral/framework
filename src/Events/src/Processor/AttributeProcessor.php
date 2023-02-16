<?php

declare(strict_types=1);

namespace Spiral\Events\Processor;

use Spiral\Attributes\ReaderInterface;
use Spiral\Events\Attribute\Listener;
use Spiral\Events\ListenerFactoryInterface;
use Spiral\Events\ListenerRegistryInterface;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;

#[TargetAttribute(Listener::class)]
final class AttributeProcessor extends AbstractProcessor implements TokenizationListenerInterface
{
    /** @var array<class-string, Listener[]> */
    private array $attributes = [];
    private bool $collected = false;

    public function __construct(
        TokenizerListenerRegistryInterface $listenerRegistry,
        private readonly ReaderInterface $reader,
        private readonly ListenerFactoryInterface $factory,
        private readonly ?ListenerRegistryInterface $registry = null,
    ) {
        // Look for Spiral\Events\Attribute\Listener attribute only when ListenerRegistry provided by container
        if ($this->registry !== null) {
            $listenerRegistry->addListener($this);
        }
    }

    public function process(): void
    {
        if ($this->registry === null) {
            return;
        }

        if (!$this->collected) {
            throw new \RuntimeException(\sprintf('Tokenizer did not finalize %s listener.', self::class));
        }

        foreach ($this->attributes as $listener => $attributes) {
            foreach ($attributes as $attribute) {
                $method = $this->getMethod($listener, $attribute->method ?? '__invoke');

                $events = (array)($attribute->event ?? $this->getEventFromTypeDeclaration($method));
                foreach ($events as $event) {
                    $this->registry->addListener(
                        event: $event,
                        listener: $this->factory->create($listener, $method->getName()),
                        priority: $attribute->priority
                    );
                }
            }
        }
    }

    public function listen(\ReflectionClass $class): void
    {
        $attrs = $this->reader->getClassMetadata($class, Listener::class);

        foreach ($attrs as $attr) {
            $this->attributes[$class->getName()][] = $attr;
        }

        foreach ($class->getMethods() as $method) {
            $attrs = $this->reader->getFunctionMetadata($method, Listener::class);

            foreach ($attrs as $attr) {
                $attr->method = $method->getName();
                $this->attributes[$class->getName()][] = $attr;
            }
        }
    }

    public function finalize(): void
    {
        $this->collected = true;
    }
}
