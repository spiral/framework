<?php

declare(strict_types=1);

namespace Spiral\Tests\Distribution;

use Spiral\Distribution\Manager;
use Spiral\Distribution\Resolver\StaticResolver;

/**
 * @group unit
 */
class ManagerTestCase extends TestCase
{
    /**
     * @var StaticResolver
     */
    private $resolver;

    /**
     * @var Manager
     */
    private $manager;

    public function setUp(): void
    {
        $this->resolver = new StaticResolver($this->uri('localhost'));

        $this->manager = new Manager();
        $this->manager->add(Manager::DEFAULT_RESOLVER, $this->resolver);

        parent::setUp();
    }

    public function testDefaultResolver(): void
    {
        $this->assertSame($this->resolver, $this->manager->resolver());
    }

    public function testResolverByName(): void
    {
        $this->assertSame($this->resolver, $this->manager->resolver('default'));
    }

    public function testUnknownResolver(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->manager->resolver('unknown');
    }

    public function testAddedResolver(): void
    {
        $this->manager->add('known', $this->resolver);

        $this->assertSame($this->resolver, $this->manager->resolver('known'));
    }

    public function testIterator(): void
    {
        $manager = clone $this->manager;

        $resolvers = \iterator_to_array($manager->getIterator());
        $this->assertSame([Manager::DEFAULT_RESOLVER => $this->resolver], $resolvers);

        $manager->add('example', $this->resolver);

        $resolvers = \iterator_to_array($manager->getIterator());
        $this->assertSame([Manager::DEFAULT_RESOLVER => $this->resolver, 'example' => $this->resolver], $resolvers);
    }

    public function testCount(): void
    {
        $manager = clone $this->manager;

        $this->assertSame(1, $manager->count());

        $manager->add('example', $this->resolver);

        $this->assertSame(2, $manager->count());
    }
}
