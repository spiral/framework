<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

use Spiral\Components\ORM\Schemas\EntitySchema;
use Spiral\Components\Tokenizer\Tokenizer;
use Spiral\Core\Component;

class SchemaReader extends Component
{
    /**
     * ORM class names.
     */
    const DATA_ENTITY = 'Spiral\Components\DataEntity';
    const ENTITY      = 'Spiral\Components\ORM\Entity';

    /**
     * Schema generating configuration.
     *
     * @var array
     */
    protected $config = array();

    /**
     * Found entity schemas.
     *
     * @var array
     */
    protected $entities = array();

    /**
     * Map (pivot) tables has to be created.
     *
     * @var array
     */
    protected $mapTables = array();

    /**
     * New ORM Schema reader instance.
     *
     * @param array     $config
     * @param Tokenizer $tokenizer
     */
    public function __construct(array $config, Tokenizer $tokenizer)
    {
        $this->config = $config;

        foreach ($tokenizer->getClasses(self::ENTITY) as $class => $definition)
        {
            if ($class == self::ENTITY)
            {
                continue;
            }

            $this->entities[$class] = EntitySchema::make(array(
                'class'     => $class,
                'ormSchema' => $this
            ));
        }
    }

    /**
     * All fetched entity schemas.
     *
     * @return EntitySchema[]
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Get EntitySchema by class name.
     *
     * @param string $class Class name.
     * @return null|EntitySchema
     */
    public function getEntity($class)
    {
        if ($class == self::ENTITY)
        {
            return EntitySchema::make(array(
                'class'     => self::ENTITY,
                'ormSchema' => $this
            ));
        }

        if (!isset($this->entities[$class]))
        {
            return null;
        }

        return $this->entities[$class];
    }

    /**
     * Get mutators for column with specified abstractType.
     *
     * @param string $columnType Column type.
     * @return array
     */
    public function findMutator($columnType)
    {
        return isset($this->config['mutators'][$columnType]) ? $this->config['mutators'][$columnType] : array();
    }
}
