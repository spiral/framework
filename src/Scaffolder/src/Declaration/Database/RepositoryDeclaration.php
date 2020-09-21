<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration\Database;

use Cycle\ORM\Select\Repository;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\DependedInterface;

class RepositoryDeclaration extends ClassDeclaration implements DependedInterface
{
    /**
     * @param string $name
     * @param string $comment
     */
    public function __construct(string $name, string $comment = '')
    {
        parent::__construct($name, 'Repository', [], $comment);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [Repository::class => null];
    }
}
