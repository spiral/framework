<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Spiral\Tests\Console\Fixtures\Attribute\WithDescriptionCommand;
use Spiral\Tests\Console\Fixtures\Attribute\WithHelpCommand;
use Spiral\Tests\Console\Fixtures\Attribute\WithNameCommand;

class AttributeTest extends BaseTest
{
    public function testCommandWithName(): void
    {
        $core = $this->getCore($this->getStaticLocator([
            WithNameCommand::class
        ]));

        $this->assertSame(
            'attribute-with-name',
            $core->run(command: 'attribute-with-name')->getOutput()->fetch()
        );
    }

    public function testCommandWithDescription(): void
    {
        $core = $this->getCore($this->getStaticLocator([
            WithDescriptionCommand::class
        ]));

        $this->assertSame(
            'Some description text',
            $core->run(command: 'attribute-with-description')->getOutput()->fetch()
        );
    }

    public function testCommandWithHelp(): void
    {
        $core = $this->getCore($this->getStaticLocator([
            WithHelpCommand::class
        ]));

        $this->assertSame(
            'Some help message',
            $core->run(command: 'attribute-with-help')->getOutput()->fetch()
        );
    }
}
