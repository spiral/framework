<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Schemas;

use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\SchemaBuilder;

interface RelationSchemaInterface
{
    /**
     * New RelationSchema instance.
     *
     * @param SchemaBuilder $builder
     * @param ModelSchema   $model
     * @param string        $name
     * @param array         $definition
     */
    public function __construct(SchemaBuilder $builder, ModelSchema $model, $name, array $definition);

    /**
     * Relation name.
     *
     * @return string
     */
    public function getName();

    /**
     * Relation type.
     *
     * @return int
     */
    public function getType();

    /**
     * Check if relationship has equivalent based on declared definition, default behaviour will
     * select polymorphic equivalent if target declared as interface.
     *
     * @return bool
     */
    public function hasEquivalent();

    /**
     * Get definition for equivalent (usually polymorphic relationship).
     *
     * @return array
     * @throws ORMException
     */
    public function createEquivalent();

    /**
     * Relation definition contains request to be reverted.
     *
     * @return bool
     */
    public function isInversable();

    /**
     * Inverse relation.
     *
     * @throws ORMException
     */
    public function inverseRelation();

    /**
     * Create all required relation columns, indexes and constraints.
     */
    public function buildSchema();

    /**
     * Pack relation data into normalized structured to be used in cached ORM schema.
     *
     * @return array
     */
    public function normalizeSchema();
}