<?php

declare(strict_types=1);

namespace Spiral\Tests\Events;

use PHPUnit\Framework\TestCase;
use Spiral\Attributes\Factory;
use Spiral\Events\Attribute\Listener;
use Spiral\Events\ListenerLocator;
use Spiral\Tests\Events\Fixtures\Listener\ClassAndMethodAttribute;
use Spiral\Tests\Events\Fixtures\Listener\ClassAttribute;
use Spiral\Tests\Events\Fixtures\Listener\ClassAttributeWithParameters;
use Spiral\Tests\Events\Fixtures\Listener\MethodAttribute;
use Spiral\Tests\Events\Fixtures\Listener\MethodAttributeWithParameters;
use Spiral\Tests\Events\Fixtures\Listener\WithoutAttribute;
use Spiral\Tokenizer\ScopedClassesInterface;

final class ListenerLocatorTest extends TestCase
{
    /**
     * @dataProvider listenersDataProvider
     */
    public function testFindListeners(string $listener, array $expected): void
    {
        $tokenizerLocator = $this->createMock(ScopedClassesInterface::class);
        $tokenizerLocator
            ->method('getScopedClasses')
            ->willReturn([
                $listener => new \ReflectionClass($listener),
            ]);

        $locator = new ListenerLocator($tokenizerLocator, (new Factory())->create());

        $result = [];
        foreach ($locator->findListeners() as $attr) {
            $result[] = $attr;
        }

        $this->assertEquals($expected, $result);
    }

    public function listenersDataProvider(): \Traversable
    {
        yield [ClassAndMethodAttribute::class, [new Listener(method: 'onFooEvent'), new Listener(method: 'onBarEvent')]];
        yield [ClassAttribute::class, [new Listener()]];
        yield [ClassAttributeWithParameters::class, [new Listener(method: 'customMethod')]];
        yield [MethodAttribute::class, [new Listener(method: '__invoke')]];
        yield [MethodAttributeWithParameters::class, [new Listener(method: 'customMethod')]];
        yield [WithoutAttribute::class, []];
    }
}
