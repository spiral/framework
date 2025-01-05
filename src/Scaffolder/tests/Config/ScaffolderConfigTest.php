<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Config;

use Spiral\Scaffolder\Bootloader\ScaffolderBootloader;
use Spiral\Scaffolder\Config\ScaffolderConfig;
use Spiral\Scaffolder\Declaration\BootloaderDeclaration;
use Spiral\Scaffolder\Exception\ScaffolderException;
use Spiral\Tests\Scaffolder\BaseTestCase;

class ScaffolderConfigTest extends BaseTestCase
{
    public function testDefaultBaseNamespace(): void
    {
        /** @var ScaffolderBootloader $scaffolder */
        $scaffolder = $this->app->get(ScaffolderBootloader::class);

        $scaffolder->addDeclaration('changing-namespace', []);

        /** @var ScaffolderConfig $config */
        $config = $this->app->get(ScaffolderConfig::class);

        self::assertSame('Spiral\\Tests\\Scaffolder\\App', (new \ReflectionMethod($config, 'baseNamespace'))->invoke($config, 'changing-namespace'));
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
        self::assertSame('', $ref->invoke($config, 'null-namespace'));
        self::assertSame('', $ref->invoke($config, 'empty-namespace'));
        self::assertSame('Test', $ref->invoke($config, 'overridden-namespace'));
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

        self::assertSame('ChangedNamespace', $ref->invoke($config, BootloaderDeclaration::TYPE, 'namespace'));
        self::assertSame('CustomPostfix', $ref->invoke($config, BootloaderDeclaration::TYPE, 'postfix'));
        self::assertSame('OtherClass', $ref->invoke($config, BootloaderDeclaration::TYPE, 'class'));
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

        self::assertSame('ChangedNamespace', $ref->invoke($config, BootloaderDeclaration::TYPE, 'namespace'));
        self::assertSame('Bootloader', $ref->invoke($config, BootloaderDeclaration::TYPE, 'postfix'));
        self::assertSame(BootloaderDeclaration::class, $ref->invoke($config, BootloaderDeclaration::TYPE, 'class'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('declarationDirectoryDataProvider')]
    public function testDeclarationDirectory(array $config, string $expected): void
    {
        $config = new ScaffolderConfig($config);

        self::assertSame($expected, $config->declarationDirectory('some'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('classFilenameDataProvider')]
    public function testClassFilename(array $config, string $expected, string $namespace): void
    {
        $config = new ScaffolderConfig($config);

        self::assertSame($expected, $config->classFilename('foo', 'Test', $namespace));
    }

    public static function declarationDirectoryDataProvider(): \Traversable
    {
        yield [['directory' => 'foo'], 'foo'];
        yield [
            [
                'directory' => 'foo',
                'defaults' => [
                    'declarations' => ['some' => []]
                ]
            ],
            'foo'
        ];
        yield [
            [
                'directory' => 'foo',
                'defaults' => [
                    'declarations' => ['some' => ['directory' => null]]
                ]
            ],
            'foo'
        ];
        yield [
            [
                'directory' => 'foo',
                'defaults' => [
                    'declarations' => ['some' => ['directory' => '']]
                ]
            ],
            'foo'
        ];
        yield [
            [
                'directory' => 'foo',
                'defaults' => [
                    'declarations' => ['some' => ['directory' => 'bar']]
                ]
            ],
            'bar'
        ];
        yield [
            [
                'directory' => 'foo',
                'declarations' => ['some' => []]
            ],
            'foo'
        ];
        yield [
            [
                'directory' => 'foo',
                'declarations' => ['some' => ['directory' => null]]
            ],
            'foo'
        ];
        yield [
            [
                'directory' => 'foo',
                'declarations' => ['some' => ['directory' => '']]
            ],
            'foo'
        ];
        yield [
            [
                'directory' => 'foo',
                'declarations' => ['some' => ['directory' => 'bar']]
            ],
            'bar'
        ];
        yield [
            [
                'directory' => 'foo',
                'declarations' => ['some' => ['directory' => 'baz']],
                'defaults' => [
                    'declarations' => ['some' => ['directory' => 'bar']]
                ]
            ],
            'baz'
        ];
    }

    public static function classFilenameDataProvider(): \Traversable
    {
        yield [
            [
                'directory' => 'foo',
                'defaults' => [
                    'declarations' => ['foo' => ['class' => 'bar']]
                ]
            ],
            'foo/App/Test/Test.php',
            'App\\Test'
        ];
        yield [
            [
                'directory' => 'foo',
                'defaults' => [
                    'declarations' => ['foo' => ['postfix' => 'Controller']]
                ]
            ],
            'foo/App/Test/TestController.php',
            'App\\Test'
        ];
        yield [
            [
                'directory' => 'foo',
                'defaults' => [
                    'declarations' => ['foo' => ['postfix' => 'Controller', 'directory' => 'baz']]
                ]
            ],
            'baz/App/Test/TestController.php',
            'App\\Test'
        ];
        yield [
            [
                'directory' => 'foo',
                'declarations' => ['foo' => ['postfix' => 'Controller', 'directory' => 'changed']],
                'defaults' => [
                    'declarations' => ['foo' => ['postfix' => 'Controller', 'directory' => 'baz']]
                ]
            ],
            'changed/App/Test/TestController.php',
            'App\\Test'
        ];
    }
}
