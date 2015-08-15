<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor\Generators\Prototypes;

/**
 * Generate abstract entity with it's schema, fields and pre-declared validations. Can be used
 * by ORM and ODM entity generators.
 */
abstract class AbstractEntity extends AbstractGenerator
{
    /**
     * @var array
     */
    protected $schema = [];

    /**
     * @var array
     */
    protected $validates = [];

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $this->class->property('fillable', ["@var array"])->setDefault(true, []);

        $this->class->property('hidden', ["@var array"])->setDefault(true, []);

        $this->class->property('schema', ["Entity schema.", "", "@var array"])->setDefault(
            true,
            $this->schema
        );

        $this->class->property('validates', ["@var array"])->setDefault(true, $this->validates);
    }

    /**
     * Add new field to entity.
     *
     * @param string $field
     * @param string $type
     * @return $this
     */
    public function addField($field, $type)
    {
        $this->schema[$field] = $type;
        $this->validates[$field] = [];

        $this->class->property('schema')->setDefault(true, $this->schema);
        $this->class->property('validates')->setDefault(true, $this->validates);

        return $this;
    }
}