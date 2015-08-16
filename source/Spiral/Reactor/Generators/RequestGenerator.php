<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor\Generators;

use Spiral\Files\FilesInterface;
use Spiral\Http\RequestFilter;
use Spiral\Reactor\Generators\Prototypes\AbstractService;

/**
 * Generates entity for RequestFilter. Provides ability to generate init method and pre-populate
 * schema using type mapping.
 */
class RequestGenerator extends AbstractService
{
    /**
     * {@inheritdoc}
     *
     * Provides ability to perform type mapping (into setters).
     */
    protected $options = [
        'namespace' => '',
        'postfix'   => '',
        'directory' => '',
        'mapping'   => []
    ];

    /**
     * @var array
     */
    protected $schema = [];

    /**
     * For class tooltips.
     *
     * @var array
     */
    protected $types = [];

    /**
     * @var array
     */
    protected $setters = [];

    /**
     * @var array
     */
    protected $validates = [];

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $this->file->addUse(RequestFilter::class);
        $this->class->setParent('RequestFilter');

        $this->class->property('schema', ["Input schema.", "", "@var array"])->setDefault(
            true,
            $this->schema
        );

        $this->class->property('setters', ["@var array"])->setDefault(true, $this->setters);
        $this->class->property('validates', ["@var array"])->setDefault(true, $this->validates);
    }

    /**
     * Add new field to request and generate default filters and validations if type presented in
     * mapping.
     *
     * @param string $field
     * @param string $type
     * @param string $source
     * @param string $origin
     */
    public function addField($field, $type, $source, $origin = null)
    {
        if (!isset($this->options['mapping'][$type])) {
            $this->schema[$field] = $source . ':' . ($origin ? $origin : $field);
            $this->types[$field] = $type;
            $this->updateProperties();

            return;
        }

        $definition = $this->options['mapping'][$type];

        //Source can depend on type
        $source = $definition['source'];
        $this->schema[$field] = $source . ':' . ($origin ? $origin : $field);

        if (!empty($definition['setter'])) {
            //Pre-defined setter
            $this->setters[$field] = $definition['setter'];
        }

        if (!empty($definition['validates'])) {
            //Pre-defined validation
            $this->validates[$field] = $definition['validates'];
        }

        $this->types[$field] = !empty($definition['type']) ? $definition['type'] : $type;

        $this->updateProperties();
    }

    /**
     * {@inheritdoc}
     */
    public function render($mode = FilesInterface::READONLY, $ensureDirectory = true)
    {
        iF (!empty($this->class->getComment())) {
            //Blank line
            $this->class->setComment([""], true);
        }

        //Adding types
        foreach ($this->types as $field => $type) {
            $this->class->setComment([
                "@property {$type} \${$field}"
            ], true);
        }

        return parent::render($mode, $ensureDirectory);
    }

    /**
     * Update generated property values.
     */
    private function updateProperties()
    {
        $this->class->property('schema')->setDefault(true, $this->schema);
        $this->class->property('validates')->setDefault(true, $this->validates);
        $this->class->property('setters')->setDefault(true, $this->setters);
    }
}