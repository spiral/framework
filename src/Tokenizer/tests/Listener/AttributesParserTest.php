<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Listener;

use PHPUnit\Framework\TestCase;
use Spiral\Attributes\AttributeReader;
use Spiral\Tests\Tokenizer\Classes\Listeners\CommandInterfaceListener;
use Spiral\Tests\Tokenizer\Classes\Listeners\CommandListener;
use Spiral\Tests\Tokenizer\Classes\Listeners\ControllerListener;
use Spiral\Tests\Tokenizer\Classes\Listeners\ConstantListener;
use Spiral\Tests\Tokenizer\Classes\Listeners\ParameterListener;
use Spiral\Tests\Tokenizer\Classes\Listeners\RouteListener;
use Spiral\Tests\Tokenizer\Classes\Targets\ConsoleCommandInterface;
use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetClass;
use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetConstant;
use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetMethod;
use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetParameter;
use Spiral\Tests\Tokenizer\Fixtures\Attributes\WithTargetProperty;
use Spiral\Tests\Tokenizer\Fixtures\TestInterface;
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

        $this->assertSame(ControllerListener::class, $definitions[0]->listenerClass);
        $this->assertSame(WithTargetClass::class, $definitions[0]->target->getName());
        $this->assertInstanceOf(\Attribute::class, $definitions[0]->attribute);
        $this->assertSame(
            \Attribute::TARGET_CLASS,
            $definitions[0]->attribute->flags
        );
        $this->assertNull($definitions[0]->scope);
    }

    public function testParseTargetMethodAttribute(): void
    {
        $definitions = \iterator_to_array(
            $this->parser->parse(new RouteListener())
        );

        $this->assertCount(2, $definitions);

        $this->assertSame(RouteListener::class, $definitions[0]->listenerClass);
        $this->assertSame(WithTargetMethod::class, $definitions[0]->target->getName());
        $this->assertInstanceOf(\Attribute::class, $definitions[0]->attribute);
        $this->assertSame(
            \Attribute::TARGET_METHOD,
            $definitions[0]->attribute->flags
        );
        $this->assertNull($definitions[0]->scope);


        $this->assertSame(WithTargetMethod::class, $definitions[1]->target->getName());
        $this->assertSame('routes', $definitions[1]->scope);
    }

    public function testParseTargetPropertyAttribute(): void
    {
        $definitions = \iterator_to_array(
            $this->parser->parse(new CommandListener())
        );

        $this->assertSame(CommandListener::class, $definitions[0]->listenerClass);
        $this->assertSame(WithTargetProperty::class, $definitions[0]->target->getName());
        $this->assertInstanceOf(\Attribute::class, $definitions[0]->attribute);
        $this->assertSame(
            \Attribute::TARGET_PROPERTY,
            $definitions[0]->attribute->flags
        );
        $this->assertNull($definitions[0]->scope);
    }

    public function testParseTargetConstantAttribute(): void
    {
        $definitions = \iterator_to_array(
            $this->parser->parse(new ConstantListener())
        );

        $this->assertSame(ConstantListener::class, $definitions[0]->listenerClass);
        $this->assertSame(WithTargetConstant::class, $definitions[0]->target->getName());
        $this->assertInstanceOf(\Attribute::class, $definitions[0]->attribute);
        $this->assertSame(
            \Attribute::TARGET_CLASS_CONSTANT,
            $definitions[0]->attribute->flags
        );
        $this->assertNull($definitions[0]->scope);
    }

    public function testParseTargetParameterAttribute(): void
    {
        $definitions = \iterator_to_array(
            $this->parser->parse(new ParameterListener())
        );

        $this->assertSame(ParameterListener::class, $definitions[0]->listenerClass);
        $this->assertSame(WithTargetParameter::class, $definitions[0]->target->getName());
        $this->assertInstanceOf(\Attribute::class, $definitions[0]->attribute);
        $this->assertSame(
            \Attribute::TARGET_PARAMETER,
            $definitions[0]->attribute->flags
        );
        $this->assertNull($definitions[0]->scope);
    }

    public function testParseTargetInterfaceAttribute(): void
    {
        $definitions = \iterator_to_array(
            $this->parser->parse(new CommandInterfaceListener())
        );

        $this->assertSame(CommandInterfaceListener::class, $definitions[0]->listenerClass);
        $this->assertSame(ConsoleCommandInterface::class, $definitions[0]->target->getName());
        $this->assertInstanceOf(\ReflectionClass::class, $definitions[0]->target);
        $this->assertNull($definitions[0]->scope);
    }
}
