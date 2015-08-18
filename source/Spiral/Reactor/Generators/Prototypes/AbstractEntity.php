<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor\Generators\Prototypes;

use Spiral\Files\FilesInterface;

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
     * @var bool
     */
    protected $showFillable = true;

    /**
     * @var bool
     */
    protected $showHidden = true;

    /**
     * @var bool
     */
    protected $showDefaults = false;

    /**
     * @var bool
     */
    protected $showIndexes = false;

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

        //Both ORM and ODM has indexes and defaults property
        $this->class->property('defaults', ["@var array"])->setDefault(true, []);
        $this->class->property('indexes', ["@var array"])->setDefault(true, []);
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

        //We can force validations for all our fields
        $this->validates[$field] = ['notEmpty'];

        $this->class->property('schema')->setDefault(true, $this->schema);
        $this->class->property('validates')->setDefault(true, $this->validates);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render($mode = FilesInterface::READONLY, $ensureDirectory = true)
    {
        if (!$this->showFillable) {
            $this->class->removeProperty('fillable');
        }

        if (!$this->showHidden) {
            $this->class->removeProperty('hidden');
        }

        if (!$this->showDefaults) {
            $this->class->removeProperty('defaults');
        }

        if (!$this->showIndexes) {
            $this->class->removeProperty('indexes');
        }

        return parent::render($mode, $ensureDirectory);
    }
}