<?php

namespace Spiral\Tests\Tokenizer;

use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Spiral\Tests\Tokenizer\Interfaces\BadInterface;
use Spiral\Tokenizer\InterfaceLocator;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tests\Tokenizer\Interfaces\InterfaceA;
use Spiral\Tests\Tokenizer\Interfaces\InterfaceB;
use Spiral\Tests\Tokenizer\Interfaces\InterfaceC;
use Spiral\Tests\Tokenizer\Interfaces\Inner\InterfaceD;
use Spiral\Tests\Tokenizer\Fixtures\TestInterface;
use Spiral\Tests\Tokenizer\Fixtures\TestTrait;
use Spiral\Tokenizer\Tokenizer;

class InterfaceLocatorTest extends TestCase
{
    public function testInterfacesAll(): void
    {
        $tokenizer = $this->getTokenizer();

        //Direct loading
        $classes = $tokenizer->interfaceLocator()->getInterfaces();

        $this->assertArrayHasKey(InterfaceA::class, $classes);
        $this->assertArrayHasKey(InterfaceB::class, $classes);
        $this->assertArrayHasKey(InterfaceC::class, $classes);
        $this->assertArrayHasKey(InterfaceD::class, $classes);

        //Excluded
        $this->assertArrayNotHasKey(InterfaceXX::class, $classes);
        $this->assertArrayNotHasKey(BadInterface::class, $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Tokenizer\Interfaces\Bad_Interface', $classes);
    }


    public function testInterfacesByInterface(): void
    {
        $tokenizer = $this->getTokenizer();

        //By interface
        $classes = $tokenizer->interfaceLocator()->getInterfaces(TestInterface::class);

        $this->assertArrayHasKey(InterfaceB::class, $classes);
        $this->assertArrayHasKey(InterfaceC::class, $classes);

        $this->assertArrayNotHasKey(InterfaceA::class, $classes);
        $this->assertArrayNotHasKey(InterfaceD::class, $classes);
    }

    public function testLoggerErrors(): void
    {
        $tokenizer = $this->getTokenizer();

        //By class
        $locator = $tokenizer->interfaceLocator();
        $logger = new class extends AbstractLogger
        {
            private $messages = [];

            public function log($level, $message, array $context = []): void
            {
                $this->messages[] = compact('level', 'message');
            }

            public function getMessages()
            {
                return $this->messages;
            }
        };

        /**
         * @var InterfaceLocator $locator
         */
        $locator->setLogger($logger);

        $locator->getInterfaces(InterfaceB::class);

        $this->assertStringContainsString(
            ' has includes and excluded from analysis',
            $logger->getMessages()[0]['message']
        );
    }

    protected function getTokenizer(): Tokenizer
    {
        $config = new TokenizerConfig([
            'directories' => [__DIR__],
            'exclude' => ['Excluded']
        ]);

        return new Tokenizer($config);
    }
}
