<?php

namespace Spiral\Tests\Tokenizer;

use PHPUnit\Framework\TestCase;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Reflection\ReflectionArgument;
use Spiral\Tokenizer\Reflection\ReflectionInvocation;
use Spiral\Tokenizer\Tokenizer;

class InvocationsTest extends TestCase
{
    protected function someFunction()
    {
        $result = $this->sampleMethod('hello world');
        print_r(self::sampleMethod($result . 'plus'));
    }

    public function testInstance(): void
    {
        $invocations = $this->getInvocations();
        self::assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        self::assertInstanceOf(ReflectionInvocation::class, $invocation1);
        self::assertInstanceOf(ReflectionInvocation::class, $invocation2);
    }

    public function testClass(): void
    {
        $invocations = $this->getInvocations();
        self::assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        self::assertSame(self::class, $invocation1->getClass());
        self::assertSame(self::class, $invocation2->getClass());
    }

    public function testName(): void
    {
        $invocations = $this->getInvocations();
        self::assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        self::assertSame('sampleMethod', $invocation1->getName());
        self::assertSame('sampleMethod', $invocation2->getName());
    }

    public function testFilename(): void
    {
        $invocations = $this->getInvocations();
        self::assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        self::assertSame(str_replace('\\', '/', __FILE__), $invocation1->getFilename());
        self::assertSame(str_replace('\\', '/', __FILE__), $invocation2->getFilename());
    }

    public function testLine(): void
    {
        $invocations = $this->getInvocations();
        self::assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        self::assertSame(15, $invocation1->getLine());
        self::assertSame(16, $invocation2->getLine());
    }

    public function testLevel(): void
    {
        $invocations = $this->getInvocations();
        self::assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        self::assertSame(0, $invocation1->getLevel());
        self::assertSame(1, $invocation2->getLevel());
    }

    public function testOperator(): void
    {
        $invocations = $this->getInvocations();
        self::assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        self::assertSame('->', $invocation1->getOperator());
        self::assertSame('::', $invocation2->getOperator());
    }

    public function testIsMethod(): void
    {
        $invocations = $this->getInvocations();
        self::assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        self::assertTrue($invocation1->isMethod());
        self::assertTrue($invocation2->isMethod());
    }

    public function testCountArguments(): void
    {
        $invocations = $this->getInvocations();
        self::assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        self::assertSame(1, $invocation1->countArguments());
        self::assertSame(1, $invocation2->countArguments());
    }

    public function testSimpleArgument(): void
    {
        $invocations = $this->getInvocations();
        self::assertCount(2, $invocations);

        $invocation1 = $invocations[0];

        $argument = $invocation1->getArgument(0);

        self::assertInstanceOf(ReflectionArgument::class, $argument);

        self::assertSame(ReflectionArgument::STRING, $argument->getType());
        self::assertSame("'hello world'", $argument->getValue());
        self::assertSame("hello world", $argument->stringValue());
    }

    public function testVariableArgument(): void
    {
        $invocations = $this->getInvocations();
        self::assertCount(2, $invocations);

        $invocation2 = $invocations[1];

        $argument = $invocation2->getArgument(0);

        self::assertInstanceOf(ReflectionArgument::class, $argument);

        self::assertSame(ReflectionArgument::EXPRESSION, $argument->getType());
        self::assertSame('$result.\'plus\'', $argument->getValue());
    }

    public function testSource(): void
    {
        $invocations = $this->getInvocations();
        self::assertCount(2, $invocations);

        $invocation1 = $invocations[0];
        $invocation2 = $invocations[1];

        self::assertSame('$this->sampleMethod(\'hello world\')', $invocation1->getSource());
        self::assertSame('self::sampleMethod($result . \'plus\')', $invocation2->getSource());
    }

    protected static function sampleMethod(string $string)
    {
    }

    /**
     * @return ReflectionInvocation[]
     * @throws \ReflectionException
     */
    protected function getInvocations(): array
    {
        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__],
            'exclude'     => []
        ]));

        $locator = $tokenizer->invocationLocator();

        $method = new \ReflectionMethod($this, 'sampleMethod');

        return $locator->getInvocations($method);
    }
}
