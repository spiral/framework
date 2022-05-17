<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Spiral\Console\Command;

class CommandDeclaration extends AbstractDeclaration
{
    public const TYPE = 'command';

    public function setAlias(string $name): void
    {
        $this->class->getConstant('NAME')->setValue($name);
    }

    public function setDescription(string $description): void
    {
        $this->class->getConstant('DESCRIPTION')->setValue($description);
    }

    /**
     * Declare default command body.
     */
    public function declare(): void
    {
        $this->namespace->addUse(Command::class);

        $this->class->setExtends(Command::class);

        $this->class->addConstant('NAME', $this->class->getName())->setProtected();
        $this->class->addConstant('DESCRIPTION', $this->class->getName())->setProtected();
        $this->class->addConstant('ARGUMENTS', [])->setProtected();
        $this->class->addConstant('OPTIONS', [])->setProtected();

        $this->class
            ->addMethod('perform')
            ->setProtected()
            ->setReturnType('void')
            ->addComment('Perform command');
    }
}
