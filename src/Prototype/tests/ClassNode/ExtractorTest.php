<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\ClassNode;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Prototype\Exception\ClassNotDeclaredException;
use Spiral\Prototype\NodeExtractor;

class ExtractorTest extends TestCase
{
    /**
     * @throws \ReflectionException
     * @throws ClassNotDeclaredException
     */
    public function testNoClass(): void
    {
        $this->expectException(ClassNotDeclaredException::class);
        $this->getExtractor()->extract(dirname(__DIR__) . '/Fixtures/noClass.php', []);
    }

    private function getExtractor(): NodeExtractor
    {
        $container = new Container();

        return $container->get(NodeExtractor::class);
    }
}
