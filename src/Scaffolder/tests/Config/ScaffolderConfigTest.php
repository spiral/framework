<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Config;

use Spiral\Scaffolder\Bootloader\ScaffolderBootloader;
use Spiral\Scaffolder\Config\ScaffolderConfig;
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
}
