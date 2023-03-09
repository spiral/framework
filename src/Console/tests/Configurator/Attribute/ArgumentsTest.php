<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Configurator\Attribute;

use PHPUnit\Framework\TestCase;
use Spiral\Attributes\Factory;
use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Configurator\Attribute\Parser;
use Spiral\Console\Exception\ConfiguratorException;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;

final class ArgumentsTest extends TestCase
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
                #[Argument]
                private int $arg;
            }
        ));

        $this->assertTrue($result->arguments[0]->isRequired());
        $this->assertFalse($result->arguments[0]->isArray());
        $this->assertSame('arg', $result->arguments[0]->getName());
        $this->assertSame('', $result->arguments[0]->getDescription());
        $this->assertNull($result->arguments[0]->getDefault());
    }

    public function testWithDefaultValue(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Argument]
                private int $arg = 1;
            }
        ));

        $this->assertFalse($result->arguments[0]->isRequired());
        $this->assertFalse($result->arguments[0]->isArray());
        $this->assertSame('arg', $result->arguments[0]->getName());
        $this->assertSame('', $result->arguments[0]->getDescription());
        $this->assertSame(1, $result->arguments[0]->getDefault());
    }

    public function testNullable(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Argument]
                private ?int $arg;
            }
        ));

        $this->assertFalse($result->arguments[0]->isRequired());
        $this->assertFalse($result->arguments[0]->isArray());
        $this->assertSame('arg', $result->arguments[0]->getName());
        $this->assertSame('', $result->arguments[0]->getDescription());
        $this->assertNull($result->arguments[0]->getDefault());
    }

    public function testWithName(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Argument(name: 'customName')]
                private int $arg;
            }
        ));

        $this->assertTrue($result->arguments[0]->isRequired());
        $this->assertFalse($result->arguments[0]->isArray());
        $this->assertSame('customName', $result->arguments[0]->getName());
        $this->assertSame('', $result->arguments[0]->getDescription());
        $this->assertNull($result->arguments[0]->getDefault());
    }

    public function testWithDescription(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Argument(description: 'Some description')]
                private int $arg;
            }
        ));

        $this->assertTrue($result->arguments[0]->isRequired());
        $this->assertFalse($result->arguments[0]->isArray());
        $this->assertSame('arg', $result->arguments[0]->getName());
        $this->assertSame('Some description', $result->arguments[0]->getDescription());
        $this->assertNull($result->arguments[0]->getDefault());
    }

    public function testWithSuggestedValue(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Argument(suggestedValues: [1, 0])]
                private int $arg;
            }
        ));

        $suggestions = new CompletionSuggestions();
        $result->arguments[0]->complete(
            new CompletionInput(),
            $suggestions
        );

        $this->assertTrue($result->arguments[0]->isRequired());
        $this->assertFalse($result->arguments[0]->isArray());
        $this->assertSame('arg', $result->arguments[0]->getName());
        $this->assertSame('', $result->arguments[0]->getDescription());
        $this->assertNull($result->arguments[0]->getDefault());
        $this->assertSame('1', $suggestions->getValueSuggestions()[0]->getValue());
        $this->assertSame('0', $suggestions->getValueSuggestions()[1]->getValue());
    }

    public function testArrayRequired(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Argument]
                private array $arg;
            }
        ));

        $this->assertTrue($result->arguments[0]->isRequired());
        $this->assertTrue($result->arguments[0]->isArray());
        $this->assertSame('arg', $result->arguments[0]->getName());
        $this->assertSame('', $result->arguments[0]->getDescription());
        $this->assertSame([], $result->arguments[0]->getDefault());
    }

    public function testArrayNotRequired(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Argument]
                private ?array $arg;
            }
        ));

        $this->assertFalse($result->arguments[0]->isRequired());
        $this->assertTrue($result->arguments[0]->isArray());
        $this->assertSame('arg', $result->arguments[0]->getName());
        $this->assertSame('', $result->arguments[0]->getDescription());
        $this->assertSame([], $result->arguments[0]->getDefault());
    }

    public function testArrayShouldBeLast(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Argument]
                private ?array $arg;

                #[Argument]
                private int $otherArg;
            }
        ));

        $this->assertTrue($result->arguments[1]->isArray());
        $this->assertSame('arg', $result->arguments[1]->getName());

        $this->assertFalse($result->arguments[0]->isArray());
        $this->assertSame('otherArg', $result->arguments[0]->getName());
    }

    public function testUnionTypeWithBuiltInAndNot(): void
    {
        $result = $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Argument]
                private int|\stdClass $arg;
            }
        ));

        $this->assertTrue($result->arguments[0]->isRequired());
        $this->assertFalse($result->arguments[0]->isArray());
        $this->assertSame('arg', $result->arguments[0]->getName());
        $this->assertSame('', $result->arguments[0]->getDescription());
        $this->assertNull($result->arguments[0]->getDefault());
    }

    public function testNotBuiltType(): void
    {
        $this->expectException(ConfiguratorException::class);
        $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Argument]
                private \stdClass $arg;
            }
        ));
    }

    public function testReflectionIntersectionType(): void
    {
        $this->expectException(ConfiguratorException::class);
        $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Argument]
                private \stdClass&\Traversable $arg;
            }
        ));
    }

    public function testTwoArrayArguments(): void
    {
        $this->expectException(ConfiguratorException::class);
        $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Argument]
                private array $arg;

                #[Argument]
                private array $otherArg;
            }
        ));
    }

    public function testArgumentWithObjectType(): void
    {
        $this->expectException(ConfiguratorException::class);
        $this->parser->parse(new \ReflectionClass(
            new #[AsCommand(name: 'foo')] class {
                #[Argument]
                private object $arg;
            }
        ));
    }
}
