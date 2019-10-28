<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Cycle;

use Cycle\ORM\Exception\ORMException;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Cycle\ORM\Select;
use Spiral\Core\Container\InjectorInterface;

final class RepositoryInjector implements InjectorInterface
{
    /** @var ORMInterface */
    private $orm;

    /** @var array */
    private $mapping = [];

    /**
     * @param ORMInterface $orm
     */
    public function __construct(ORMInterface $orm)
    {
        $this->orm = $orm;

        foreach ($orm->getSchema()->getRoles() as $role) {
            $repository = $orm->getSchema()->define($role, Schema::REPOSITORY);
            if ($repository !== Select\Repository::class && $repository !== null) {
                $this->mapping[$repository] = $role;
            }
        }
    }

    /**
     * @param \ReflectionClass $class
     * @param string|null      $context
     * @return object|void
     */
    public function createInjection(\ReflectionClass $class, string $context = null)
    {
        if (!isset($this->mapping[$class->getName()])) {
            throw new ORMException("Unable to find Entity role for repository {$class->getName()}");
        }

        return $this->orm->getRepository($this->mapping[$class->getName()]);
    }
}
