<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ODM;

use Spiral\Components\ODM\Schemas\CollectionSchema;
use Spiral\Components\ODM\Schemas\DocumentSchema;
use Spiral\Components\Tokenizer\Tokenizer;
use Spiral\Core\Component;

class SchemaReader extends Component
{
    /**
     * ODM class names.
     */
    const COMPOSITABLE = 'Spiral\Components\ODM\CompositableInterface';
    const COLLECTION   = 'Spiral\Components\ODM\Collection';
    const COMPOSITOR   = 'Spiral\Components\ODM\Accessors\Compositor';
    const DATA_ENTITY  = 'Spiral\Components\DataEntity';
    const DOCUMENT     = 'Spiral\Components\ODM\Document';

    /**
     * Schema generating configuration.
     *
     * @var array
     */
    protected $config = array();

    /**
     * Found document schemas.
     *
     * @var DocumentSchema[]
     */
    protected $documents = array();

    /**
     * Collections schemas (associated documents).
     *
     * @var CollectionSchema[]
     */
    protected $collections = array();

    /**
     * New ODM Schema reader instance.
     *
     * @param array     $config
     * @param Tokenizer $tokenizer
     */
    public function __construct(array $config, Tokenizer $tokenizer)
    {
        $this->config = $config;

        foreach ($tokenizer->getClasses(self::DOCUMENT) as $class => $definition)
        {
            if ($class == self::DOCUMENT)
            {
                continue;
            }

            $this->documents[$class] = DocumentSchema::make(array(
                'class'     => $class,
                'odmSchema' => $this
            ));
        }

        foreach ($this->getDocuments() as $documentSchema)
        {
            if (!$collection = $documentSchema->getCollection())
            {
                //Skip embedded models
                continue;
            }

            //Getting fully specified collection name (with specified db)
            $collection = $documentSchema->getDatabase() . '/' . $collection;

            if (!isset($this->collections[$collection]))
            {
                $primaryDocument = $this->getDocument($documentSchema->primaryClass());

                if ($documentSchema->getCollection() == $primaryDocument->getCollection())
                {
                    //Child document use same collection as parent?
                    $this->collections[$collection] = CollectionSchema::make(array(
                        'name'            => $primaryDocument->getCollection(),
                        'database'        => $primaryDocument->getDatabase(),
                        'classDefinition' => $primaryDocument->classDefinition(),
                        'primaryClass'    => $primaryDocument->getClass(),
                        'odmSchema'       => $this
                    ));
                }
                else
                {
                    $this->collections[$collection] = CollectionSchema::make(array(
                        'name'            => $documentSchema->getCollection(),
                        'database'        => $documentSchema->getDatabase(),
                        'classDefinition' => $documentSchema->classDefinition(),
                        'primaryClass'    => $documentSchema->getClass(),
                        'odmSchema'       => $this
                    ));
                }
            }
        }
    }

    /**
     * All fetched document schemas.
     *
     * @return DocumentSchema[]
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Collections schema (associated documents).
     *
     * @return CollectionSchema[]
     */
    public function getCollections()
    {
        return $this->collections;
    }

    /**
     * Get DocumentSchema by class name.
     *
     * @param string $class Class name.
     * @return null|DocumentSchema
     */
    public function getDocument($class)
    {
        if ($class == self::DOCUMENT)
        {
            return new DocumentSchema(self::DOCUMENT, $this);
        }

        if (!isset($this->documents[$class]))
        {
            return null;
        }

        return $this->documents[$class];
    }

    /**
     * Get mutators for field with specified abstractType.
     *
     * @param string $abstractType Field type.
     * @return array
     */
    public function getMutators($abstractType)
    {
        return isset($this->config['mutators'][$abstractType])
            ? $this->config['mutators'][$abstractType]
            : array();
    }

    /**
     * Normalize ODM schema and export it to be used by ODM component and all documents.
     *
     * @return array
     */
    public function normalizeSchema()
    {
        $schema = array();

        foreach ($this->collections as $collection)
        {
            $schema[$collection->getDatabase() . '/' . $collection->getName()] = array(
                ODM::C_DEFINITION => $this->classDefinition($collection->classDefinition())
            );
        }

        foreach ($this->documents as $document)
        {
            if ($document->isAbstract())
            {
                continue;
            }

            $documentSchema = array();
            if ($document->getCollection())
            {
                $documentSchema[ODM::D_COLLECTION] = $document->getCollection();
                $documentSchema[ODM::D_DB] = $document->getDatabase();
            }

            $documentSchema[ODM::D_DEFAULTS] = $document->getDefaults();
            $documentSchema[ODM::D_HIDDEN] = $document->getHidden();
            $documentSchema[ODM::D_SECURED] = $document->getSecured();
            $documentSchema[ODM::D_FILLABLE] = $document->getFillable();

            $documentSchema[ODM::D_MUTATORS] = $document->getMutators();
            $documentSchema[ODM::D_VALIDATES] = $document->getValidates();
            $documentSchema[ODM::D_MESSAGES] = $document->getMessages();

            $documentSchema[ODM::D_AGGREGATIONS] = array();
            foreach ($document->getAggregations() as $name => $aggregation)
            {
                $documentSchema[ODM::D_AGGREGATIONS][$name] = array(
                    ODM::AGR_TYPE       => $aggregation['type'],
                    ODM::AGR_COLLECTION => $aggregation['collection'],
                    ODM::AGR_DB         => $aggregation['database'],
                    ODM::AGR_QUERY      => $aggregation['query']
                );
            }

            $documentSchema[ODM::D_COMPOSITIONS] = array_keys($document->getCompositions());
            $schema[$document->getClass()] = $documentSchema;
        }

        return $schema;
    }

    /**
     * Normalizing class detection definition.
     *
     * @param mixed $classDefinition
     * @return array
     */
    protected function classDefinition($classDefinition)
    {
        if (is_string($classDefinition))
        {
            //Single collection class
            return $classDefinition;
        }

        return array(
            ODM::DEFINITION         => $classDefinition['type'],
            ODM::DEFINITION_OPTIONS => $classDefinition['options']
        );
    }
}