<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

class NamespacedNameTest extends AbstractCommandTestCase
{
    private const CLASS_NAME = '\\Spiral\\Tests\\Scaffolder\\App\\Controller\\Namespaced\\SampleController';

    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testScaffold(): void
    {
        $this->className = self::CLASS_NAME;
        $this->console()->run('create:controller', [
            'name' => 'namespaced/sample',
            '--comment' => 'Sample Controller',
            '-a' => ['index', 'save'],
        ]);

        clearstatcache();
        self::assertTrue(class_exists(self::CLASS_NAME));

        $reflection = new \ReflectionClass(self::CLASS_NAME);

        self::assertStringContainsString('strict_types=1', $this->files()->read($reflection->getFileName()));
        self::assertStringContainsString('Sample Controller', $reflection->getDocComment());
        self::assertTrue($reflection->hasMethod('index'));
        self::assertTrue($reflection->hasMethod('save'));
    }
}
