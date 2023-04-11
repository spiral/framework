<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Config;

use Spiral\Scaffolder\Bootloader\ScaffolderBootloader;
use Spiral\Scaffolder\Config\ScaffolderConfig;
use Spiral\Scaffolder\Declaration\BootloaderDeclaration;
use Spiral\Scaffolder\Exception\ScaffolderException;
use Spiral\Tests\Scaffolder\BaseTest;

class ScaffolderConfigTest extends BaseTest
{
    public function testDefaultBaseNamespace(): void
    {
        /** @var ScaffolderBootloader $scaffolder */
        $scaffolder = $this->app->get(ScaffolderBootloader::class);

        $scaffolder->addDeclaration('changing-namespace', []);

        /** @var ScaffolderConfig $config */
        $config = $this->app->get(ScaffolderConfig::class);

        $this->assertSame(
            'Spiral\\Tests\\Scaffolder\\App',
            (new \ReflectionMethod($config, 'baseNamespace'))->invoke($config, 'changing-namespace')
        );
    }

    public function testChangingBaseNamespace(): void
    {
        /** @var ScaffolderBootloader $scaffolder */
        $scaffolder = $this->app->get(ScaffolderBootloader::class);

        $scaffolder->addDeclaration('null-namespace', ['baseNamespace' => null]);
        $scaffolder->addDeclaration('empty-namespace', ['baseNamespace' => '']);
        $scaffolder->addDeclaration('overridden-namespace', ['baseNamespace' => 'Test']);

        /** @var ScaffolderConfig $config */
        $config = $this->app->get(ScaffolderConfig::class);

        $ref = new \ReflectionMethod($config, 'baseNamespace');
        $this->assertSame('', $ref->invoke($config, 'null-namespace'));
        $this->assertSame('', $ref->invoke($config, 'empty-namespace'));
        $this->assertSame('Test', $ref->invoke($config, 'overridden-namespace'));
    }

    public function testUndefinedDeclarationException(): void
    {
        /** @var ScaffolderConfig $config */
        $config = $this->app->get(ScaffolderConfig::class);
        $ref = new \ReflectionMethod($config, 'getOption');

        $this->expectException(ScaffolderException::class);
        $this->expectExceptionMessage('Undefined declaration \'undefined\'.');
        $ref->invoke($config, 'undefined', 'namespace');
    }

    public function testOverrideDefaultDeclaration(): void
    {
        $this->app->getContainer()->bind(ScaffolderConfig::class, new ScaffolderConfig([
            'declarations' => [
                BootloaderDeclaration::TYPE => [
                    'namespace' => 'ChangedNamespace',
                    'postfix' => 'CustomPostfix',
                    'class' => 'OtherClass',
                ],
            ],
            'defaults' => [
                'declarations' => [
                    BootloaderDeclaration::TYPE => [
                        'namespace' => 'Bootloader',
                        'postfix' => 'Bootloader',
                        'class' => BootloaderDeclaration::class,
                    ],
                ],
            ]
        ]));

        /** @var ScaffolderConfig $config */
        $config = $this->app->get(ScaffolderConfig::class);
        $ref = new \ReflectionMethod($config, 'getOption');

        $this->assertSame('ChangedNamespace', $ref->invoke($config, BootloaderDeclaration::TYPE, 'namespace'));
        $this->assertSame('CustomPostfix', $ref->invoke($config, BootloaderDeclaration::TYPE, 'postfix'));
        $this->assertSame('OtherClass', $ref->invoke($config, BootloaderDeclaration::TYPE, 'class'));
    }

    public function testPartialOverrideDefaultDeclaration(): void
    {
        $this->app->getContainer()->bind(ScaffolderConfig::class, new ScaffolderConfig([
            'declarations' => [
                BootloaderDeclaration::TYPE => [
                    'namespace' => 'ChangedNamespace',
                ],
            ],
            'defaults' => [
                'declarations' => [
                    BootloaderDeclaration::TYPE => [
                        'namespace' => 'Bootloader',
                        'postfix' => 'Bootloader',
                        'class' => BootloaderDeclaration::class,
                    ],
                ],
            ]
        ]));

        /** @var ScaffolderConfig $config */
        $config = $this->app->get(ScaffolderConfig::class);
        $ref = new \ReflectionMethod($config, 'getOption');

        $this->assertSame('ChangedNamespace', $ref->invoke($config, BootloaderDeclaration::TYPE, 'namespace'));
        $this->assertSame('Bootloader', $ref->invoke($config, BootloaderDeclaration::TYPE, 'postfix'));
        $this->assertSame(BootloaderDeclaration::class, $ref->invoke($config, BootloaderDeclaration::TYPE, 'class'));
    }
}
