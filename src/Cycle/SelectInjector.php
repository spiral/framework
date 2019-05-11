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
use Cycle\ORM\Select;
use Spiral\Core\Container\InjectorInterface;

final class SelectInjector implements InjectorInterface
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
     * @param \ReflectionClass $class
     * @param string|null      $context
     * @return object|void
     */
    public function createInjection(\ReflectionClass $class, string $context = null)
    {
        if (!$this->orm->getSchema()->defines($context)) {
            throw new ORMException("Cycle schema is not initiated or role missing, run `cycle`.");
        }

        $select = new Select($this->orm, $context);
        $select->constrain($this->orm->getSource($context)->getConstrain());

        return $select;
    }
}