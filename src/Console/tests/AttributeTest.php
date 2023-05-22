<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tests\Console\Fixtures\Attribute\WithDescriptionCommand;
use Spiral\Tests\Console\Fixtures\Attribute\WithHelpCommand;
use Spiral\Tests\Console\Fixtures\Attribute\WithNameCommand;
use Spiral\Tests\Console\Fixtures\Attribute\WithSymfonyAttributeCommand;

final class AttributeTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->container->bind(ReaderInterface::class, AttributeReader::class);
    }

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

    public function testCommandWithSymfonyAttribute(): void
    {
        $core = $this->getCore($this->getStaticLocator([
            WithSymfonyAttributeCommand::class
        ]));

        $this->assertSame(
            'Some description text|attribute-with-sf-command-attr',
            $core->run(command: 'attribute-with-sf-command-attr')->getOutput()->fetch()
        );
    }
}
