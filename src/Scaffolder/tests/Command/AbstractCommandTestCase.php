<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

use Spiral\Console\Console;
use Spiral\Files\FilesInterface;
use Spiral\Tests\Scaffolder\BaseTestCase;

abstract class AbstractCommandTestCase extends BaseTestCase
{
    protected ?string $className = null;

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->className) {
            $this->deleteDeclaration($this->className);
        }
    }

    protected function deleteDeclaration(string $class): void
    {
        if (class_exists($class)) {
            try {
                $reflection = new \ReflectionClass($class);
                $this->files()->delete($reflection->getFileName());
            } catch (\Throwable $exception) {
                var_dump($exception->getMessage());
            }
        }
    }

    /**
     * @throws \Throwable
     */
    protected function console(): Console
    {
        return $this->app->get(Console::class);
    }

    /**
     * @throws \Throwable
     */
    protected function files(): FilesInterface
    {
        return $this->app->get(FilesInterface::class);
    }
}
