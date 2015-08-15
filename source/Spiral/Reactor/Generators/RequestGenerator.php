<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor\Generators;

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

    public function addField($field, $type, $source = 'data')
    {
    }
}