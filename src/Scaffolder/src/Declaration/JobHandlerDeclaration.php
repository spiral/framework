<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Spiral\Queue\JobHandler;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\DependedInterface;

class JobHandlerDeclaration extends ClassDeclaration implements DependedInterface
{
    public function __construct(string $name, string $comment = '')
    {
        parent::__construct($name, 'JobHandler', [], $comment);

        $this->declareStructure();
    }

    public function getDependencies(): array
    {
        return [JobHandler::class => null];
    }

    /**
     * Declare constants and boot method.
     */
    private function declareStructure(): void
    {
        $method = $this->method('invoke');
        $method->setPublic();
        $method->setReturn('void');
    }
}
