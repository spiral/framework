<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Configurator\Attribute;

use PHPUnit\Framework\TestCase;
use Spiral\Attributes\Factory;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Configurator\Attribute\Parser;
use Spiral\Console\Exception\ConfiguratorException;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Input\InputOption;

final class OptionsTest extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        $this->parser = new Parser((new Factory())->create());
    }

    public function testRequired(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Option(mode: InputOption::VALUE_REQUIRED)]
                private int $option;
            }
        ));

        $this->assertSame('option', $result->options[0]->getName());
        $this->assertNull($result->options[0]->getShortcut());
        $this->assertTrue($result->options[0]->isValueRequired());
        $this->assertFalse($result->options[0]->isArray());
        $this->assertFalse($result->options[0]->isNegatable());
        $this->assertSame('', $result->options[0]->getDescription());
        $this->assertNull($result->options[0]->getDefault());
    }

    public function testDefaultValue(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Option(mode: InputOption::VALUE_OPTIONAL)]
                private string $option = 'some';
            }
        ));

        $this->assertSame('option', $result->options[0]->getName());
        $this->assertNull($result->options[0]->getShortcut());
        $this->assertFalse($result->options[0]->isValueRequired());
        $this->assertFalse($result->options[0]->isArray());
        $this->assertFalse($result->options[0]->isNegatable());
        $this->assertSame('', $result->options[0]->getDescription());
        $this->assertSame('some', $result->options[0]->getDefault());
    }

    public function testNullable(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Option(mode: InputOption::VALUE_OPTIONAL)]
                private ?string $option;
            }
        ));

        $this->assertSame('option', $result->options[0]->getName());
        $this->assertNull($result->options[0]->getShortcut());
        $this->assertFalse($result->options[0]->isValueRequired());
        $this->assertFalse($result->options[0]->isArray());
        $this->assertFalse($result->options[0]->isNegatable());
        $this->assertSame('', $result->options[0]->getDescription());
        $this->assertNull($result->options[0]->getDefault());
    }

    public function testWithName(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Option(name: 'customName', mode: InputOption::VALUE_OPTIONAL)]
                private ?string $option;
            }
        ));

        $this->assertSame('customName', $result->options[0]->getName());
        $this->assertNull($result->options[0]->getShortcut());
        $this->assertFalse($result->options[0]->isValueRequired());
        $this->assertFalse($result->options[0]->isArray());
        $this->assertFalse($result->options[0]->isNegatable());
        $this->assertSame('', $result->options[0]->getDescription());
        $this->assertNull($result->options[0]->getDefault());
    }

    public function testWithShortcut(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Option(shortcut: 't', mode: InputOption::VALUE_OPTIONAL)]
                private ?string $option;
            }
        ));

        $this->assertSame('option', $result->options[0]->getName());
        $this->assertSame('t', $result->options[0]->getShortcut());
        $this->assertFalse($result->options[0]->isValueRequired());
        $this->assertFalse($result->options[0]->isArray());
        $this->assertFalse($result->options[0]->isNegatable());
        $this->assertSame('', $result->options[0]->getDescription());
        $this->assertNull($result->options[0]->getDefault());
    }

    public function testWithDescription(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Option(description: 'Some description', mode: InputOption::VALUE_OPTIONAL)]
                private ?string $option;
            }
        ));

        $this->assertSame('option', $result->options[0]->getName());
        $this->assertNull($result->options[0]->getShortcut());
        $this->assertFalse($result->options[0]->isValueRequired());
        $this->assertFalse($result->options[0]->isArray());
        $this->assertFalse($result->options[0]->isNegatable());
        $this->assertSame('Some description', $result->options[0]->getDescription());
        $this->assertNull($result->options[0]->getDefault());
    }

    public function testModeOptionalArray(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Option(mode: InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY)]
                private array $option = [];
            }
        ));

        $this->assertSame('option', $result->options[0]->getName());
        $this->assertNull($result->options[0]->getShortcut());
        $this->assertFalse($result->options[0]->isValueRequired());
        $this->assertTrue($result->options[0]->isArray());
        $this->assertFalse($result->options[0]->isNegatable());
        $this->assertSame('', $result->options[0]->getDescription());
        $this->assertSame([], $result->options[0]->getDefault());
    }

    public function testModeRequiredArray(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Option(mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)]
                private array $option = [];
            }
        ));

        $this->assertSame('option', $result->options[0]->getName());
        $this->assertNull($result->options[0]->getShortcut());
        $this->assertTrue($result->options[0]->isValueRequired());
        $this->assertTrue($result->options[0]->isArray());
        $this->assertFalse($result->options[0]->isNegatable());
        $this->assertSame('', $result->options[0]->getDescription());
        $this->assertSame([], $result->options[0]->getDefault());
    }

    public function testModeValueNegatable(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Option(mode: InputOption::VALUE_NEGATABLE)]
                private bool $option;
            }
        ));

        $this->assertSame('option', $result->options[0]->getName());
        $this->assertNull($result->options[0]->getShortcut());
        $this->assertFalse($result->options[0]->isValueRequired());
        $this->assertFalse($result->options[0]->isArray());
        $this->assertTrue($result->options[0]->isNegatable());
        $this->assertSame('', $result->options[0]->getDescription());
        $this->assertNull($result->options[0]->getDefault());
    }

    public function testModeValueNone(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Option(mode: InputOption::VALUE_NONE)]
                private bool $option;
            }
        ));

        $this->assertSame('option', $result->options[0]->getName());
        $this->assertNull($result->options[0]->getShortcut());
        $this->assertFalse($result->options[0]->isValueRequired());
        $this->assertFalse($result->options[0]->isArray());
        $this->assertFalse($result->options[0]->acceptValue());
        $this->assertSame('', $result->options[0]->getDescription());
        $this->assertFalse($result->options[0]->getDefault());
    }

    public function testWithSuggestedValue(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Option(mode: InputOption::VALUE_REQUIRED, suggestedValues: [1, 0])]
                private int $option;
            }
        ));

        $suggestions = new CompletionSuggestions();
        $result->options[0]->complete(
            new CompletionInput(),
            $suggestions
        );

        $this->assertSame('option', $result->options[0]->getName());
        $this->assertNull($result->options[0]->getShortcut());
        $this->assertTrue($result->options[0]->isValueRequired());
        $this->assertFalse($result->options[0]->isArray());
        $this->assertTrue($result->options[0]->acceptValue());
        $this->assertSame('', $result->options[0]->getDescription());
        $this->assertNull($result->options[0]->getDefault());
        $this->assertSame('1', $suggestions->getValueSuggestions()[0]->getValue());
        $this->assertSame('0', $suggestions->getValueSuggestions()[1]->getValue());
    }

    public function testUnionTypeWithBuiltInAndNot(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Option(mode: InputOption::VALUE_OPTIONAL)]
                private int|\stdClass $option;
            }
        ));

        $this->assertSame('option', $result->options[0]->getName());
        $this->assertNull($result->options[0]->getShortcut());
        $this->assertFalse($result->options[0]->isValueRequired());
        $this->assertFalse($result->options[0]->isArray());
        $this->assertTrue($result->options[0]->acceptValue());
        $this->assertSame('', $result->options[0]->getDescription());
        $this->assertNull($result->options[0]->getDefault());
    }

    public function testNotBuiltType(): void
    {
        $this->expectException(ConfiguratorException::class);
        $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Option]
                private \stdClass $option;
            }
        ));
    }

    public function testReflectionIntersectionType(): void
    {
        $this->expectException(ConfiguratorException::class);
        $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Option]
                private \stdClass&\Traversable $option;
            }
        ));
    }
}
