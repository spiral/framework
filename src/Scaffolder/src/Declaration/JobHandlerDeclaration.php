<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Spiral\Queue\JobHandler;

class JobHandlerDeclaration extends AbstractDeclaration implements HasInstructions
{
    public const TYPE = 'jobHandler';

    public function declare(): void
    {
        $this->namespace->addUse(JobHandler::class);
        $this->class->setExtends(JobHandler::class);
        $this->class->setFinal();

        $method = $this->class
            ->addMethod('invoke')
            ->setPublic()
            ->setReturnType('void');

        $method->addParameter('id')
            ->setType('string');

        $method->addParameter('payload')
            ->setType('mixed');

        $method->addParameter('headers')
            ->setType('array');
    }

    public function getInstructions(): array
    {
        return [
            'Read more about Job handlers in the documentation: https://spiral.dev/docs/queue-jobs',
        ];
    }
}
