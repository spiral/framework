<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

use ReflectionClass;
use ReflectionException;
use Spiral\Core\CoreInterface;
use Throwable;

class BootloaderTest extends AbstractCommandTest
{
    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testScaffold(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Bootloader\\SampleBootloader';

        $this->console()->run('create:bootloader', [
            'name' => 'sample',
            '--comment' => 'Sample Bootloader'
        ]);

        clearstatcache();
        $this->assertTrue(\class_exists($class));

        $reflection = new ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString('strict_types=1', $content);
        $this->assertStringContainsString('Sample Bootloader', $reflection->getDocComment());
        $this->assertStringContainsString('{project-name}', $content);
        $this->assertStringContainsString('@author {author-name}', $content);
        $this->assertTrue($reflection->hasMethod('boot'));
        $this->assertTrue($reflection->isFinal());

        $this->assertTrue($reflection->hasConstant('BINDINGS'));
        $this->assertTrue($reflection->hasConstant('SINGLETONS'));
        $this->assertTrue($reflection->hasConstant('DEPENDENCIES'));

        $this->assertEquals([], $reflection->getReflectionConstant('BINDINGS')->getValue());
        $this->assertEquals([], $reflection->getReflectionConstant('SINGLETONS')->getValue());
        $this->assertEquals([], $reflection->getReflectionConstant('DEPENDENCIES')->getValue());
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testScaffoldWithCustomNamespace(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Custom\\Bootloader\\SampleBootloader';

        $this->console()->run('create:bootloader', [
            'name' => 'sample',
            '--namespace' => 'Spiral\\Tests\\Scaffolder\\App\\Custom\\Bootloader'
        ]);

        clearstatcache();
        $this->assertTrue(\class_exists($class));

        $reflection = new ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString(
            'App/Custom/Bootloader/SampleBootloader.php',
            \str_replace('\\', '/', $reflection->getFileName())
        );
        $this->assertStringContainsString('App\Custom\Bootloader', $content);
    }

    public function testScaffoldForDomainBootloader(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Bootloader\\SampleDomainBootloader';

        $this->console()->run('create:bootloader', [
            'name' => 'SampleDomain',
            '--domain' => true
        ]);

        clearstatcache();
        $this->assertTrue(\class_exists($class));

        $reflection = new ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString(
            'Spiral\Bootloader\DomainBootloader',
            $content
        );

        //$this->assertTrue($reflection->hasConstant('INTERCEPTORS'));
        $this->assertTrue($reflection->hasConstant('SINGLETONS'));

        $this->assertEquals([
            CoreInterface::class => ['Spiral\Tests\Scaffolder\App\Bootloader\SampleDomainBootloader', 'domainCore'],
        ], $reflection->getConstant('SINGLETONS'));
    }
}
