<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer;

use Psr\Log\LoggerInterface;
use Spiral\Tokenizer\AbstractLocator;
use Spiral\Tokenizer\Exception\LocatorException;
use Symfony\Component\Finder\Finder;

final class AbstractLocatorTest extends \PHPUnit\Framework\TestCase
{
    private Finder $finder;

    protected function setUp(): void
    {
        $this->finder = new Finder();
        $this->finder->files()->in([__DIR__ . '/Classes'])->name('*.php');
    }

    public function testHasIncludesMessage(): void
    {
        $class = $this->getLocator(true);
        $ref = new \ReflectionMethod($class, 'availableReflections');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with(
            $this->stringContains(' has includes and excluded from analysis')
        );
        $class->setLogger($logger);

        \iterator_to_array($ref->invoke($class));
    }

    public function testNoHasIncludesMessageSent(): void
    {
        $class = $this->getLocator();
        $ref = new \ReflectionMethod($class, 'availableReflections');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('warning');
        $class->setLogger($logger);

        \iterator_to_array($ref->invoke($class));
    }

    public function testClassReflectionMessage(): void
    {
        $class = $this->getLocator(true);
        $ref = new \ReflectionMethod($class, 'classReflection');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error')->with(
            $this->stringContains("Class 'foo' can not be loaded")
        );
        $class->setLogger($logger);

        try {
            $ref->invoke($class, 'foo');
        } catch (LocatorException) {
        }
    }

    public function testNoClassReflectionMessageSent(): void
    {
        $class = $this->getLocator();
        $ref = new \ReflectionMethod($class, 'classReflection');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('error');
        $class->setLogger($logger);

        try {
            $ref->invoke($class, 'foo');
        } catch (LocatorException) {
        }
    }

    public function testEnumReflectionMessage(): void
    {
        $class = $this->getLocator(true);
        $ref = new \ReflectionMethod($class, 'enumReflection');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error')->with(
            $this->stringContains("Enum 'foo' can not be loaded")
        );
        $class->setLogger($logger);

        try {
            $ref->invoke($class, 'foo');
        } catch (LocatorException) {
        }
    }

    public function testNoEnumReflectionMessageSent(): void
    {
        $class = $this->getLocator();
        $ref = new \ReflectionMethod($class, 'enumReflection');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('error');
        $class->setLogger($logger);

        try {
            $ref->invoke($class, 'foo');
        } catch (LocatorException) {
        }
    }

    private function getLocator(bool $debug = false): AbstractLocator
    {
        return new class($this->finder, $debug) extends AbstractLocator {};
    }
}
