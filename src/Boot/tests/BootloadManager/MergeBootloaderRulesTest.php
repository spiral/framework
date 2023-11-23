<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\BootloadManager;

use Spiral\Boot\Attribute\BootloaderRules;
use Spiral\Tests\Boot\Fixtures\BootloaderD;
use Spiral\Tests\Boot\Fixtures\BootloaderF;
use Spiral\Tests\Boot\Fixtures\BootloaderG;
use Spiral\Tests\Boot\Fixtures\BootloaderH;
use Spiral\Tests\Boot\Fixtures\BootloaderI;

final class MergeBootloaderRulesTest extends InitializerTestCase
{
    public function testOverrideEnabled(): void
    {
        $result = \iterator_to_array($this->initializer->init([
            BootloaderF::class => new BootloaderRules(enabled: true),
            BootloaderD::class
        ]));

        $this->assertEquals([
            BootloaderF::class => ['bootloader' => new BootloaderF(), 'options' => []],
            BootloaderD::class => ['bootloader' => new BootloaderD(), 'options' => []]
        ], $result);
    }

    public function testOverrideArgs(): void
    {
        $result = \iterator_to_array($this->initializer->init([
            BootloaderG::class => new BootloaderRules(args: ['foo' => 'bar']),
        ]));

        $this->assertEquals([
            BootloaderG::class => ['bootloader' => new BootloaderG(), 'options' => ['foo' => 'bar']]
        ], $result);
    }

    public function testMergeArgs(): void
    {
        $result = \iterator_to_array($this->initializer->init([
            BootloaderG::class => new BootloaderRules(args: ['foo' => 'bar', 'a' => 'baz'], override: false),
        ]));

        $this->assertEquals([
            BootloaderG::class => ['bootloader' => new BootloaderG(), 'options' => [
                'a' => 'baz',
                'foo' => 'bar',
                'c' => 'd'
            ]]
        ], $result);
    }

    public function testOverrideAllowEnv(): void
    {
        $ref = new \ReflectionMethod($this->initializer, 'getBootloaderRules');
        $rules = $ref->invoke(
            $this->initializer,
            BootloaderH::class,
            new BootloaderRules(allowEnv: ['foo' => 'bar'])
        );

        $this->assertEquals(['foo' => 'bar'], $rules->allowEnv);
    }

    public function testMergeAllowEnv(): void
    {
        $ref = new \ReflectionMethod($this->initializer, 'getBootloaderRules');
        $rules = $ref->invoke(
            $this->initializer,
            BootloaderH::class,
            new BootloaderRules(allowEnv: ['APP_ENV' => 'dev', 'foo' => 'bar'], override: false)
        );

        $this->assertEquals([
            'foo' => 'bar',
            'APP_ENV' => 'dev',
            'APP_DEBUG' => false,
            'RR_MODE' => ['http']
        ], $rules->allowEnv);
    }

    public function testOverrideDenyEnv(): void
    {
        $ref = new \ReflectionMethod($this->initializer, 'getBootloaderRules');
        $rules = $ref->invoke(
            $this->initializer,
            BootloaderI::class,
            new BootloaderRules(denyEnv: ['foo' => 'bar'])
        );

        $this->assertEquals(['foo' => 'bar'], $rules->denyEnv);
    }

    public function testMergeDenyEnv(): void
    {
        $ref = new \ReflectionMethod($this->initializer, 'getBootloaderRules');
        $rules = $ref->invoke(
            $this->initializer,
            BootloaderI::class,
            new BootloaderRules(denyEnv: ['DB_HOST' => 'localhost', 'foo' => 'bar'], override: false)
        );

        $this->assertEquals([
            'foo' => 'bar',
            'RR_MODE' => 'http',
            'APP_ENV' => ['production', 'prod'],
            'DB_HOST' => 'localhost',
        ], $rules->denyEnv);
    }
}
