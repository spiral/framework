<?php

namespace Spiral\Tests\Tokenizer;

use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Spiral\Tokenizer\ClassLocator;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tests\Tokenizer\Classes\ClassA;
use Spiral\Tests\Tokenizer\Classes\ClassB;
use Spiral\Tests\Tokenizer\Classes\ClassC;
use Spiral\Tests\Tokenizer\Classes\Inner\ClassD;
use Spiral\Tests\Tokenizer\Fixtures\TestInterface;
use Spiral\Tests\Tokenizer\Fixtures\TestTrait;
use Spiral\Tokenizer\Tokenizer;

class ClassLocatorTest extends TestCase
{
    public function testClassesAll()
    {
        $tokenizer = $this->getTokenizer();

        //Direct loading
        $classes = $tokenizer->classLocator()->getClasses();

        $this->assertArrayHasKey(self::class, $classes);
        $this->assertArrayHasKey(ClassA::class, $classes);
        $this->assertArrayHasKey(ClassB::class, $classes);
        $this->assertArrayHasKey(ClassC::class, $classes);
        $this->assertArrayHasKey(ClassD::class, $classes);

        //Excluded
        $this->assertArrayNotHasKey('Spiral\Tests\Tokenizer\Classes\Excluded\ClassXX', $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Tokenizer\Classes\Bad_Class', $classes);
    }

    public function testClassesByClass()
    {
        $tokenizer = $this->getTokenizer();

        //By namespace
        $classes = $tokenizer->classLocator()->getClasses(ClassD::class);

        $this->assertArrayHasKey(ClassD::class, $classes);

        $this->assertArrayNotHasKey(self::class, $classes);
        $this->assertArrayNotHasKey(ClassA::class, $classes);
        $this->assertArrayNotHasKey(ClassB::class, $classes);
        $this->assertArrayNotHasKey(ClassC::class, $classes);
    }

    public function testClassesByInterface()
    {
        $tokenizer = $this->getTokenizer();

        //By interface
        $classes = $tokenizer->classLocator()->getClasses(TestInterface::class);

        $this->assertArrayHasKey(ClassB::class, $classes);
        $this->assertArrayHasKey(ClassC::class, $classes);

        $this->assertArrayNotHasKey(self::class, $classes);
        $this->assertArrayNotHasKey(ClassA::class, $classes);
        $this->assertArrayNotHasKey(ClassD::class, $classes);
    }

    public function testClassesByTrait()
    {
        $tokenizer = $this->getTokenizer();

        //By trait
        $classes = $tokenizer->classLocator()->getClasses(TestTrait::class);

        $this->assertArrayHasKey(ClassB::class, $classes);
        $this->assertArrayHasKey(ClassC::class, $classes);

        $this->assertArrayNotHasKey(self::class, $classes);
        $this->assertArrayNotHasKey(ClassA::class, $classes);
        $this->assertArrayNotHasKey(ClassD::class, $classes);
    }

    public function testClassesByClassA()
    {
        $tokenizer = $this->getTokenizer();

        //By class
        $classes = $tokenizer->classLocator()->getClasses(ClassA::class);

        $this->assertArrayHasKey(ClassA::class, $classes);
        $this->assertArrayHasKey(ClassB::class, $classes);
        $this->assertArrayHasKey(ClassC::class, $classes);
        $this->assertArrayHasKey(ClassD::class, $classes);

        $this->assertArrayNotHasKey(self::class, $classes);
    }

    public function testClassesByClassB()
    {
        $tokenizer = $this->getTokenizer();
        $classes = $tokenizer->classLocator()->getClasses(ClassB::class);

        $this->assertArrayHasKey(ClassB::class, $classes);
        $this->assertArrayHasKey(ClassC::class, $classes);

        $this->assertArrayNotHasKey(self::class, $classes);
        $this->assertArrayNotHasKey(ClassA::class, $classes);
        $this->assertArrayNotHasKey(ClassD::class, $classes);
    }

    public function testLoggerErrors()
    {
        $tokenizer = $this->getTokenizer();

        //By class
        $locator = $tokenizer->classLocator();
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
         * @var ClassLocator $locator
         */
        $locator->setLogger($logger);

        $locator->getClasses(ClassB::class);

        $this->assertStringContainsString(
            ' has includes and excluded from analysis',
            $logger->getMessages()[0]['message']
        );
    }

    protected function getTokenizer()
    {
        $config = new TokenizerConfig([
            'directories' => [__DIR__],
            'exclude' => ['Excluded']
        ]);

        return new Tokenizer($config);
    }
}
