<?php

declare(strict_types=1);

namespace Spiral\Tests\Distribution;

use Spiral\Distribution\Manager;
use Spiral\Distribution\Resolver\StaticResolver;

#[\PHPUnit\Framework\Attributes\Group('unit')]
class ManagerTestCase extends TestCase
{
    private StaticResolver $resolver;
    private Manager $manager;

    public function testDefaultResolver(): void
    {
        self::assertSame($this->resolver, $this->manager->resolver());
    }

    public function testResolverByName(): void
    {
        self::assertSame($this->resolver, $this->manager->resolver('default'));
    }

    public function testUnknownResolver(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->manager->resolver('unknown');
    }

    public function testAddedResolver(): void
    {
        $this->manager->add('known', $this->resolver);

        self::assertSame($this->resolver, $this->manager->resolver('known'));
    }

    public function testIterator(): void
    {
        $manager = clone $this->manager;

        $resolvers = \iterator_to_array($manager->getIterator());
        self::assertSame([Manager::DEFAULT_RESOLVER => $this->resolver], $resolvers);

        $manager->add('example', $this->resolver);

        $resolvers = \iterator_to_array($manager->getIterator());
        self::assertSame([Manager::DEFAULT_RESOLVER => $this->resolver, 'example' => $this->resolver], $resolvers);
    }

    public function testCount(): void
    {
        $manager = clone $this->manager;

        self::assertCount(1, $manager);

        $manager->add('example', $this->resolver);

        self::assertCount(2, $manager);
    }

    protected function setUp(): void
    {
        $this->resolver = new StaticResolver($this->uri('localhost'));

        $this->manager = new Manager();
        $this->manager->add(Manager::DEFAULT_RESOLVER, $this->resolver);

        parent::setUp();
    }
}
