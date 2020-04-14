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
     * @param string[] $withFields
     * @return bool
     */
    public function unique($value, string $class, string $field, array $withFields = []): bool
    {
        $values = $this->withValues($withFields);
        $values[$field] = $value;

        if ($this->isProvidedByContext($values)) {
            return true;
        }

        return $this->orm->getRepository($class)->findOne($values) === null;
    }

    /**
     * @param string[] $fields
     * @return array
     */
    private function withValues(array $fields): array
    {
        $values = [];
        foreach ($fields as $field) {
            $validator = $this->getValidator();
            $value = $validator->getValue($field);
            if ((method_exists($validator, 'hasValue') && $validator->hasValue($field)) || $value !== null) {
                $values[$field] = $value;
            }
        }

        return $values;
    }

    /**
     * @param array $values
     * @return bool
     */
    private function isProvidedByContext(array $values): bool
    {
        $context = $this->getValidator()->getContext()[static::class] ?? [];
        if (!is_array($context)) {
            return false;
        }

        foreach ($values as $field => $value) {
            if (!isset($context[$field]) || $context[$field] !== $value) {
                return false;
            }
        }

        return true;
    }
}
