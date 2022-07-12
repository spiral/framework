<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use PHPUnit\Framework\TestCase;
use Spiral\Console\Config\ConsoleConfig;
use Spiral\Console\Exception\ConfigException;
use Spiral\Console\Sequence\CallableSequence;

class ConfigTest extends TestCase
{
    public function testBadSequence(): void
    {
        $this->expectException(ConfigException::class);

        $config = new ConsoleConfig([
            'sequences' => [
                'update' => [
                    []
                ],
            ],
        ]);

        iterator_to_array($config->updateSequence());
    }

    public function testForcedSequence(): void
    {
        $config = new ConsoleConfig([
            'sequences' => [
                'update' => [
                    new CallableSequence('test'),
                ],
            ],
        ]);

        $this->assertCount(1, iterator_to_array($config->updateSequence()));
    }
}
