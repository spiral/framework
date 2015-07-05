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

interface RelationSchemaInterface
{
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
     * Relation definition (declared in model schema).
     *
     * @return array
     */
    public function getDefinition();

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
    public function getEquivalentDefinition();

    /**
     * Relation definition contains request to be reverted.
     *
     * @return bool
     */
    public function hasInvertedRelation();

    /**
     * Create reverted relations in outer model or models.
     *
     * @param string $name Relation name.
     * @param int    $type Back relation type, can be required some cases.
     * @throws ORMException
     */
    public function revertRelation($name, $type = null);

    /**
     * Pack relation data into normalized structured to be used in cached ORM schema.
     *
     * @return array
     */
    public function normalizeSchema();
}