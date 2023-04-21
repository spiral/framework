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

    /**
     * @dataProvider baseDirectoryDataProvider
     */
    public function testBaseDirectory(array $config, string $expected, ?string $element = null): void
    {
        $config = new ScaffolderConfig($config);

        $this->assertSame($expected, $config->baseDirectory($element));
    }

    /**
     * @dataProvider classFilenameDataProvider
     */
    public function testClassFilename(array $config, string $expected, string $namespace): void
    {
        $config = new ScaffolderConfig($config);

        $this->assertSame($expected, $config->classFilename('foo', 'Test', $namespace));
    }

    public static function baseDirectoryDataProvider(): \Traversable
    {
        yield [['directory' => 'foo'], 'foo'];
        yield [['directory' => 'foo'], 'foo', 'some'];
        yield [
            [
                'directory' => 'foo',
                'defaults' => [
                    'declarations' => ['some' => []]
                ]
            ],
            'foo',
            'some'
        ];
        yield [
            [
                'directory' => 'foo',
                'defaults' => [
                    'declarations' => ['some' => ['directory' => null]]
                ]
            ],
            'foo',
            'some'
        ];
        yield [
            [
                'directory' => 'foo',
                'defaults' => [
                    'declarations' => ['some' => ['directory' => '']]
                ]
            ],
            'foo',
            'some'
        ];
        yield [
            [
                'directory' => 'foo',
                'defaults' => [
                    'declarations' => ['some' => ['directory' => 'bar']]
                ]
            ],
            'bar',
            'some'
        ];
        yield [
            [
                'directory' => 'foo',
                'declarations' => ['some' => []]
            ],
            'foo',
            'some'
        ];
        yield [
            [
                'directory' => 'foo',
                'declarations' => ['some' => ['directory' => null]]
            ],
            'foo',
            'some'
        ];
        yield [
            [
                'directory' => 'foo',
                'declarations' => ['some' => ['directory' => '']]
            ],
            'foo',
            'some'
        ];
        yield [
            [
                'directory' => 'foo',
                'declarations' => ['some' => ['directory' => 'bar']]
            ],
            'bar',
            'some'
        ];
        yield [
            [
                'directory' => 'foo',
                'declarations' => ['some' => ['directory' => 'baz']],
                'defaults' => [
                    'declarations' => ['some' => ['directory' => 'bar']]
                ]
            ],
            'baz',
            'some'
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
