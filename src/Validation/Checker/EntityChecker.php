<?php

declare(strict_types=1);

namespace Spiral\Validation\Checker;

use Cycle\ORM\ORMInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Validation\AbstractChecker;

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
     * @param string|int $value
     * @param string     $class
     * @return bool
     */
    public function exists($value, string $class): bool
    {
        return $this->orm->getRepository($class)->findByPK($value) !== null;
    }

    /**
     * @param mixed    $value
     * @param string   $class
     * @param string   $field
     * @param string[] $fields
     * @return bool
     */
    public function unique($value, string $class, string $field, array $fields = []): bool
    {
        /** @var array $contextual */
        $contextual = $this->getValidator()->getContext()[static::class] ?? [];

        $values = $this->validatorValues($fields);
        $values[$field] = $value;

        //Entity is passed and its value hasn't changed.
        if ($contextual !== null && $this->valuesEqual($values, $contextual)) {
            return true;
        }

        return $this->orm->getRepository($class)->findOne($values) === null;
    }

    /**
     * @param string[] $fields
     * @return array
     */
    private function validatorValues(array $fields): array
    {
        $values = [];
        foreach ($fields as $field) {
            $value = $this->getValidator()->getValue($field);
            if ($value !== null) {
                $values[$field] = $value;
            }
        }

        return $values;
    }

    /**
     * @param array $values
     * @param array $contextualValues
     * @return bool
     */
    private function valuesEqual(array $values, array $contextualValues): bool
    {
        foreach ($values as $field => $value) {
            if (!isset($contextualValues[$field]) || $contextualValues[$field] !== $value) {
                return false;
            }
        }

        return true;
    }
}
