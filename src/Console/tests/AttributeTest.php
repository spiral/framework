<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tests\Console\Fixtures\Attribute\WithAliasesCommand;
use Spiral\Tests\Console\Fixtures\Attribute\WithDescriptionCommand;
use Spiral\Tests\Console\Fixtures\Attribute\WithHelpCommand;
use Spiral\Tests\Console\Fixtures\Attribute\WithNameCommand;
use Spiral\Tests\Console\Fixtures\Attribute\WithSymfonyAttributeCommand;

final class AttributeTest extends BaseTestCase
{
    public function testCommandWithName(): void
    {
        $core = $this->getCore($this->getStaticLocator([
            WithNameCommand::class,
        ]));

        self::assertSame('attribute-with-name', $core->run(command: 'attribute-with-name')->getOutput()->fetch());
    }

    public function testCommandWithDescription(): void
    {
        $core = $this->getCore($this->getStaticLocator([
            WithDescriptionCommand::class,
        ]));

        self::assertSame('Some description text', $core->run(command: 'attribute-with-description')->getOutput()->fetch());
    }

    public function testCommandWithHelp(): void
    {
        $core = $this->getCore($this->getStaticLocator([
            WithHelpCommand::class,
        ]));

        self::assertSame('Some help message', $core->run(command: 'attribute-with-help')->getOutput()->fetch());
    }

    public function testCommandWithAliases(): void
    {
        $core = $this->getCore($this->getStaticLocator([
            WithAliasesCommand::class,
        ]));

        self::assertSame('awa,alias-for-with-aliases', $core->run(command: 'attribute-with-aliases')->getOutput()->fetch());
        self::assertSame('awa,alias-for-with-aliases', $core->run(command: 'awa')->getOutput()->fetch());
        self::assertSame('awa,alias-for-with-aliases', $core->run(command: 'alias-for-with-aliases')->getOutput()->fetch());
    }

    public function testCommandWithSymfonyAttribute(): void
    {
        $core = $this->getCore($this->getStaticLocator([
            WithSymfonyAttributeCommand::class,
        ]));

        self::assertSame('Some description text|attribute-with-sf-command-attr', $core->run(command: 'attribute-with-sf-command-attr')->getOutput()->fetch());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->container->bind(ReaderInterface::class, AttributeReader::class);
    }
}
