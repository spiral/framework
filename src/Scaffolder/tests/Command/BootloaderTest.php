<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

use ReflectionClass;
use ReflectionException;
use Throwable;

class BootloaderTest extends AbstractCommandTest
{
    private const CLASS_NAME = '\\Spiral\\Tests\\Scaffolder\\App\\Bootloader\\SampleBootloader';

    public function tearDown(): void
    {
        $this->deleteDeclaration(self::CLASS_NAME);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testScaffold(): void
    {
        $this->console()->run('create:bootloader', [
            'name'      => 'sample',
            '--comment' => 'Sample Bootloader'
        ]);

        clearstatcache();
        $this->assertTrue(class_exists(self::CLASS_NAME));

        $reflection = new ReflectionClass(self::CLASS_NAME);
        $content = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString('strict_types=1', $content);
        $this->assertStringContainsString('Sample Bootloader', $reflection->getDocComment());
        $this->assertStringContainsString('{project-name}', $content);
        $this->assertStringContainsString('@author {author-name}', $content);
        $this->assertTrue($reflection->hasMethod('boot'));

        $this->assertTrue($reflection->hasConstant('BINDINGS'));
        $this->assertTrue($reflection->hasConstant('SINGLETONS'));
        $this->assertTrue($reflection->hasConstant('DEPENDENCIES'));

        $this->assertEquals([], $reflection->getReflectionConstant('BINDINGS')->getValue());
        $this->assertEquals([], $reflection->getReflectionConstant('SINGLETONS')->getValue());
        $this->assertEquals([], $reflection->getReflectionConstant('DEPENDENCIES')->getValue());
    }
}
