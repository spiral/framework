<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Listener;

use PHPUnit\Framework\TestCase;
use Spiral\Attributes\AttributeReader;
use Spiral\Tests\Tokenizer\Classes\Listeners\ControllerListener;
use Spiral\Tests\Tokenizer\Classes\Listeners\RouteListener;
use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetClass;
use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetMethod;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\Attribute\TargetClass;
use Spiral\Tokenizer\Listener\AttributesParser;

final class AttributesParserTest extends TestCase
{
    private AttributesParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new AttributesParser(
            new AttributeReader()
        );
    }

    public function testParseTargetClassAttribute(): void
    {
        $definitions = \iterator_to_array(
            $this->parser->parse(new ControllerListener())
        );

        $this->assertInstanceOf(TargetAttribute::class, $definitions[0]);
        $this->assertSame(WithTargetClass::class, $definitions[0]->class);
        $this->assertNull($definitions[0]->scope);
    }

    public function testParseTargetMethodAttribute(): void
    {
        $definitions = \iterator_to_array(
            $this->parser->parse(new RouteListener())
        );

        $this->assertCount(2, $definitions);

        $this->assertInstanceOf(TargetAttribute::class, $definitions[0]);
        $this->assertSame(WithTargetMethod::class, $definitions[0]->class);
        $this->assertNull($definitions[0]->scope);


        $this->assertInstanceOf(TargetClass::class, $definitions[1]);
        $this->assertSame(WithTargetMethod::class, $definitions[1]->class);
        $this->assertSame('routes', $definitions[1]->scope);
    }
}
