<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Signature;

use PHPUnit\Framework\TestCase;
use Spiral\Console\Signature\Parser;

final class ParserTest extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new Parser();
    }

    public function testParse(): void
    {
        // Only name
        $result = $this->parser->parse('foo:bar');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame([], $result->arguments);
        $this->assertSame([], $result->options);
    }

    public function testParseArgument(): void
    {
        // Name with argument
        $result = $this->parser->parse('foo:bar {baz}');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame('baz', $result->arguments[0]->getName());
        $this->assertTrue($result->arguments[0]->isRequired());
        $this->assertSame([], $result->options);

        // Name with not required argument
        $result = $this->parser->parse('foo:bar {baz?}');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame('baz', $result->arguments[0]->getName());
        $this->assertFalse($result->arguments[0]->isRequired());
        $this->assertSame([], $result->options);

        // Name with not required argument with default value
        $result = $this->parser->parse('foo:bar {baz=baf}');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame('baz', $result->arguments[0]->getName());
        $this->assertSame('baf', $result->arguments[0]->getDefault());
        $this->assertFalse($result->arguments[0]->isRequired());
        $this->assertSame([], $result->options);

        // Name with not required argument with default value and description
        $result = $this->parser->parse('foo:bar { baz=baf : Argument description. }');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame('baz', $result->arguments[0]->getName());
        $this->assertSame('baf', $result->arguments[0]->getDefault());
        $this->assertSame('Argument description.', $result->arguments[0]->getDescription());
        $this->assertFalse($result->arguments[0]->isRequired());
        $this->assertSame([], $result->options);

        // Name with not required argument with nullable default value and description
        $result = $this->parser->parse('foo:bar { baz= : Argument description. }');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame('baz', $result->arguments[0]->getName());
        $this->assertNull($result->arguments[0]->getDefault());
        $this->assertSame('Argument description.', $result->arguments[0]->getDescription());
        $this->assertFalse($result->arguments[0]->isRequired());
        $this->assertSame([], $result->options);

        // Name with argument with required array
        $result = $this->parser->parse('foo:bar {baz[]}');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame('baz', $result->arguments[0]->getName());
        $this->assertTrue($result->arguments[0]->isRequired());
        $this->assertTrue($result->arguments[0]->isArray());
        $this->assertSame([], $result->options);

        // Name with argument with not required array
        $result = $this->parser->parse('foo:bar {baz[]?}');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame('baz', $result->arguments[0]->getName());
        $this->assertFalse($result->arguments[0]->isRequired());
        $this->assertTrue($result->arguments[0]->isArray());
        $this->assertSame([], $result->options);

        // Name with argument with not required array and description
        $result = $this->parser->parse('foo:bar {baz[]? : Argument description. }');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame('baz', $result->arguments[0]->getName());
        $this->assertFalse($result->arguments[0]->isRequired());
        $this->assertTrue($result->arguments[0]->isArray());
        $this->assertSame('Argument description.', $result->arguments[0]->getDescription());
        $this->assertSame([], $result->options);
    }

    public function testParseOption(): void
    {
        $result = $this->parser->parse('foo:bar {--baz}');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame([], $result->arguments);
        $this->assertSame('baz', $result->options[0]->getName());
        $this->assertFalse($result->options[0]->acceptValue());

        // Parse option with acceptable value
        $result = $this->parser->parse('foo:bar {--baz=}');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame([], $result->arguments);
        $this->assertSame('baz', $result->options[0]->getName());
        $this->assertTrue($result->options[0]->acceptValue());
        $this->assertFalse($result->options[0]->isArray());

        // Parse option with shortcut
        $result = $this->parser->parse('foo:bar {--o|baz=}');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame([], $result->arguments);
        $this->assertSame('baz', $result->options[0]->getName());
        $this->assertSame('o', $result->options[0]->getShortcut());
        $this->assertTrue($result->options[0]->acceptValue());
        $this->assertFalse($result->options[0]->isArray());

        // Parse option with acceptable array
        $result = $this->parser->parse('foo:bar {--baz[]=}');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame([], $result->arguments);
        $this->assertSame('baz', $result->options[0]->getName());
        $this->assertTrue($result->options[0]->acceptValue());
        $this->assertTrue($result->options[0]->isArray());

        // Parse option with acceptable array with shortcut
        $result = $this->parser->parse('foo:bar {--b|baz[]=}');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame([], $result->arguments);
        $this->assertSame('b', $result->options[0]->getShortcut());
        $this->assertSame('baz', $result->options[0]->getName());
        $this->assertTrue($result->options[0]->acceptValue());
        $this->assertTrue($result->options[0]->isArray());

        // Parse option with description
        $result = $this->parser->parse('foo:bar {--baz : Option description.}');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame([], $result->arguments);
        $this->assertSame('baz', $result->options[0]->getName());
        $this->assertFalse($result->options[0]->acceptValue());
        $this->assertSame('Option description.', $result->options[0]->getDescription());

        // Parse option with description with shortcut
        $result = $this->parser->parse('foo:bar {--b|baz : Option description.}');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame([], $result->arguments);
        $this->assertSame('b', $result->options[0]->getShortcut());
        $this->assertSame('baz', $result->options[0]->getName());
        $this->assertFalse($result->options[0]->acceptValue());
        $this->assertSame('Option description.', $result->options[0]->getDescription());

        // Parse option with acceptable value and description
        $result = $this->parser->parse('foo:bar {--baz= : Option description.}');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame([], $result->arguments);
        $this->assertSame('baz', $result->options[0]->getName());
        $this->assertTrue($result->options[0]->acceptValue());
        $this->assertNull($result->options[0]->getDefault());
        $this->assertSame('Option description.', $result->options[0]->getDescription());

        // Parse option with default value and description
        $result = $this->parser->parse('foo:bar {--baz=defaultValue : Option description.}');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame([], $result->arguments);
        $this->assertSame('baz', $result->options[0]->getName());
        $this->assertTrue($result->options[0]->acceptValue());
        $this->assertSame('defaultValue', $result->options[0]->getDefault());
        $this->assertSame('Option description.', $result->options[0]->getDescription());
    }

    public function testParseWithArgumentAndOption(): void
    {
        $result = $this->parser->parse('foo:bar {baf} {--baz}');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame('baf', $result->arguments[0]->getName());
        $this->assertTrue($result->arguments[0]->isRequired());
        $this->assertSame('baz', $result->options[0]->getName());
        $this->assertFalse($result->options[0]->acceptValue());

        // Parse option and argument with description
        $result = $this->parser->parse('foo:bar {baf:Argument description.} {--baz : Option description.}');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame('baf', $result->arguments[0]->getName());
        $this->assertTrue($result->arguments[0]->isRequired());
        $this->assertSame('Argument description.', $result->arguments[0]->getDescription());
        $this->assertSame('baz', $result->options[0]->getName());
        $this->assertFalse($result->options[0]->acceptValue());
        $this->assertSame('Option description.', $result->options[0]->getDescription());

        // Parse option with shortcut and argument with description
        $result = $this->parser->parse('foo:bar {baf:Argument description.} {--b|baz : Option description.}');

        $this->assertSame('foo:bar', $result->name);
        $this->assertSame('baf', $result->arguments[0]->getName());
        $this->assertTrue($result->arguments[0]->isRequired());
        $this->assertSame('Argument description.', $result->arguments[0]->getDescription());
        $this->assertSame('b', $result->options[0]->getShortcut());
        $this->assertSame('baz', $result->options[0]->getName());
        $this->assertFalse($result->options[0]->acceptValue());
        $this->assertSame('Option description.', $result->options[0]->getDescription());
    }
}
