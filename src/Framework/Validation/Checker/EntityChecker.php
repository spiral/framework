<?php

declare(strict_types=1);

namespace Spiral\Validation\Checker;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Select\Repository;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Database\Injection\Expression;
use Spiral\Validation\AbstractChecker;

/**
 * @deprecated since v2.9. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
class EntityChecker extends AbstractChecker implements SingletonInterface
{
    /**
     * {@inheritdoc}
     */
    public const MESSAGES = [
        'exists' => '[[Entity not exists.]]',
        'unique' => '[[Value should be unique.]]',
    ];

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
     * @param string|int  $value
     * @param string      $role
     * @param string|null $field
     * @param bool        $ignoreCase
     * @return bool
     */
    public function exists($value, string $role, ?string $field = null, bool $ignoreCase = false): bool
    {
        $repository = $this->orm->getRepository($role);
        if ($field === null) {
            return $repository->findByPK($value) !== null;
        }

        if ($ignoreCase && $repository instanceof Repository) {
            return $this
                ->addCaseInsensitiveWhere($repository->select(), $field, $value)
                ->fetchOne() !== null;
        }

        return $repository->findOne([$field => $value]) !== null;
    }

    /**
     * @param mixed    $value
     * @param string   $role
     * @param string   $field
     * @param string[] $withFields
     * @param bool     $ignoreCase
     * @return bool
     */
    public function unique($value, string $role, string $field, array $withFields = [], bool $ignoreCase = false): bool
    {
        $values = $this->withValues($withFields);
        $values[$field] = $value;

        if ($this->isProvidedByContext($role, $values)) {
            return true;
        }

        $repository = $this->orm->getRepository($role);

        if ($ignoreCase && $repository instanceof Repository) {
            $select = $repository->select();

            foreach ($values as $field => $fieldValue) {
                $this->addCaseInsensitiveWhere($select, $field, $fieldValue);
            }

            return $select->fetchOne() === null;
        }

        return $repository->findOne($values) === null;
    }

    /**
     * @param string[] $fields
     * @return array
     */
    private function withValues(array $fields): array
    {
        $values = [];
        foreach ($fields as $field) {
            if ($this->getValidator()->hasValue($field)) {
                $values[$field] = $this->getValidator()->getValue($field);
            }
        }

        return $values;
    }

    /**
     * @param string $role
     * @param array  $values
     * @return bool
     */
    private function isProvidedByContext(string $role, array $values): bool
    {
        $entity = $this->getValidator()->getContext();
        if (!is_object($entity) || !$this->orm->getHeap()->has($entity)) {
            return false;
        }

        $extract = $this->orm->getMapper($role)->extract($entity);
        foreach ($values as $field => $value) {
            if (!isset($extract[$field]) || $extract[$field] !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Select $select
     * @param string $field
     * @param mixed $value
     * @return Select
     */
    private function addCaseInsensitiveWhere(Select $select, string $field, $value): Select
    {
        if (!is_string($value)) {
            return $select->where($field, $value);
        }

        $queryBuilder = $select->getBuilder();

        return $select
            ->where(
                new Expression("LOWER({$queryBuilder->resolve($field)})"),
                mb_strtolower($value)
            )
        ;
    }
}
