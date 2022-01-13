<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Spiral\Console\Command;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\DependedInterface;

class CommandDeclaration extends ClassDeclaration implements DependedInterface
{
    public function __construct(string $name, string $comment = '')
    {
        parent::__construct($name, 'Command', [], $comment);

        $this->declareStructure();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [Command::class => null];
    }

    /**
     * Set command alias.
     */
    public function setAlias(string $name): void
    {
        $this->constant('NAME')->setValue($name);
    }

    public function setDescription(string $description): void
    {
        $this->constant('DESCRIPTION')->setValue($description);
    }

    /**
     * Declare default command body.
     */
    private function declareStructure(): void
    {
        $perform = $this->method('perform')->setProtected();
        $perform->setReturn('void');
        $perform->setComment('Perform command');

        $this->constant('NAME')->setProtected()->setValue('');
        $this->constant('DESCRIPTION')->setProtected()->setValue('');
        $this->constant('ARGUMENTS')->setProtected()->setValue([]);
        $this->constant('OPTIONS')->setProtected()->setValue([]);
    }
}
