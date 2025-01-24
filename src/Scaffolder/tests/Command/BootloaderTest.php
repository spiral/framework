<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

use Spiral\Interceptors\HandlerInterface;

final class BootloaderTest extends AbstractCommandTestCase
{
    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testScaffold(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Bootloader\\SampleBootloader';

        $this->console()->run('create:bootloader', [
            'name' => 'sample',
            '--comment' => 'Sample Bootloader',
        ]);

        clearstatcache();
        self::assertTrue(\class_exists($class));

        $reflection = new \ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        self::assertStringContainsString('strict_types=1', $content);
        self::assertStringContainsString('Sample Bootloader', $reflection->getDocComment());
        self::assertStringContainsString('{project-name}', $content);
        self::assertStringContainsString('@author {author-name}', $content);
        self::assertTrue($reflection->hasMethod('boot'));
        self::assertTrue($reflection->isFinal());

        self::assertTrue($reflection->hasConstant('BINDINGS'));
        self::assertTrue($reflection->hasConstant('SINGLETONS'));
        self::assertTrue($reflection->hasConstant('DEPENDENCIES'));

        self::assertEquals([], $reflection->getReflectionConstant('BINDINGS')->getValue());
        self::assertEquals([], $reflection->getReflectionConstant('SINGLETONS')->getValue());
        self::assertEquals([], $reflection->getReflectionConstant('DEPENDENCIES')->getValue());
    }

    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testScaffoldWithCustomNamespace(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Custom\\Bootloader\\SampleBootloader';

        $this->console()->run('create:bootloader', [
            'name' => 'sample',
            '--namespace' => 'Spiral\\Tests\\Scaffolder\\App\\Custom\\Bootloader',
        ]);

        clearstatcache();
        self::assertTrue(\class_exists($class));

        $reflection = new \ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        self::assertStringContainsString('App/Custom/Bootloader/SampleBootloader.php', \str_replace('\\', '/', $reflection->getFileName()));

        self::assertStringContainsString('App\Custom\Bootloader', $content);
    }

    public function testScaffoldForDomainBootloader(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Bootloader\\SampleDomainBootloader';

        $this->console()->run('create:bootloader', [
            'name' => 'SampleDomain',
            '--domain' => true,
        ]);

        clearstatcache();
        self::assertTrue(\class_exists($class));

        $reflection = new \ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        self::assertStringContainsString(\Spiral\Bootloader\DomainBootloader::class, $content);

        //$this->assertTrue($reflection->hasConstant('INTERCEPTORS'));
        self::assertTrue($reflection->hasConstant('SINGLETONS'));

        self::assertEquals([
            HandlerInterface::class => ['Spiral\Tests\Scaffolder\App\Bootloader\SampleDomainBootloader', 'domainCore'],
        ], $reflection->getConstant('SINGLETONS'));
    }

    public function testShowInstructionAfterInstallation(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Bootloader\\SampleBootloader';

        $result = $this->console()->run('create:bootloader', [
            'name' => 'sample',
            '--comment' => 'Sample Bootloader',
        ]);

        $output = $result->getOutput()->fetch();

        self::assertStringEqualsStringIgnoringLineEndings(<<<OUTPUT
            Declaration of 'SampleBootloader' has been successfully written into 'Bootloader/SampleBootloader.php'.

            Next steps:
            1. Don't forget to add your bootloader to the bootloader's list in 'Spiral\Tests\Scaffolder\App\TestApp' class
            2. Read more about bootloaders in the documentation: https://spiral.dev/docs/framework-bootloaders

            OUTPUT, $output);
    }
}
