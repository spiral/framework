<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Spiral\Queue\JobHandler;

class JobHandlerDeclaration extends AbstractDeclaration
{
    public const TYPE = 'jobHandler';

    public function declare(): void
    {
        $this->class->setExtends(JobHandler::class);

        $this->class
            ->addMethod('invoke')
            ->setPublic()
            ->setReturnType('void');
    }
}
