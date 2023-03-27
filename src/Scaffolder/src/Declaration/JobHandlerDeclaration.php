<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Spiral\Queue\JobHandler;

class JobHandlerDeclaration extends AbstractDeclaration
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
            ->setType('array');

        $method->addParameter('headers')
            ->setType('array');
    }
}
