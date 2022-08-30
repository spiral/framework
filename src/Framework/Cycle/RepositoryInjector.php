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
use ReflectionClass;
use Spiral\Core\Container\InjectorInterface;

/**
 * @deprecated since v2.9. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
final class RepositoryInjector implements InjectorInterface
{
    /** @var ORMInterface */
    private $orm;

    /**
     * @param ORMInterface $orm
     */
    public function __construct(ORMInterface $orm)
    {
        $this->orm = $orm;
    }

    /**
     * @param ReflectionClass $class
     * @param string|null     $context
     * @return object
     *
     * @throws ORMException
     */
    public function createInjection(ReflectionClass $class, string $context = null)
    {
        $schema = $this->orm->getSchema();

        foreach ($schema->getRoles() as $role) {
            $repository = $schema->define($role, Schema::REPOSITORY);

            if (
                $repository !== Select\Repository::class
                && $repository === $class->getName()
            ) {
                return $this->orm->getRepository($role);
            }
        }

        throw new ORMException(sprintf('Unable to find Entity role for repository %s', $class->getName()));
    }
}
