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

class SchemaBuilder extends Component
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
    protected $config = [];

    /**
     * Found document schemas.
     *
     * @var DocumentSchema[]
     */
    protected $documents = [];

    /**
     * Collections schemas (associated documents).
     *
     * @var CollectionSchema[]
     */
    protected $collections = [];

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

            $this->documents[$class] = new DocumentSchema($this, $class);
        }

        foreach ($this->getDocumentSchemas() as $documentSchema)
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
                    $this->collections[$collection] = new CollectionSchema(
                        $this,
                        $primaryDocument->getCollection(),
                        $primaryDocument->getDatabase(),
                        $primaryDocument->classDefinition(),
                        $primaryDocument->getClass()
                    );
                }
                else
                {

                    $this->collections[$collection] = new CollectionSchema(
                        $this,
                        $documentSchema->getCollection(),
                        $documentSchema->getDatabase(),
                        $documentSchema->classDefinition(),
                        $documentSchema->getClass()

                    );
                }
            }
        }
    }

    /**
     * All fetched document schemas.
     *
     * @return DocumentSchema[]
     */
    public function getDocumentSchemas()
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
            return new DocumentSchema($this, self::DOCUMENT);
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
            : [];
    }

    /**
     * Normalize ODM schema and export it to be used by ODM component and all documents.
     *
     * @return array
     */
    public function normalizeSchema()
    {
        $schema = [];

        foreach ($this->collections as $collection)
        {
            $schema[$collection->getDatabase() . '/' . $collection->getName()] = [
                ODM::C_DEFINITION => $this->classDefinition($collection->classDefinition())
            ];
        }

        foreach ($this->documents as $document)
        {
            if ($document->isAbstract())
            {
                continue;
            }

            $documentSchema = [];
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

            $documentSchema[ODM::D_AGGREGATIONS] = [];
            foreach ($document->getAggregations() as $name => $aggregation)
            {
                $documentSchema[ODM::D_AGGREGATIONS][$name] = [
                    ODM::AGR_TYPE       => $aggregation['type'],
                    ODM::AGR_COLLECTION => $aggregation['collection'],
                    ODM::AGR_DB         => $aggregation['database'],
                    ODM::AGR_QUERY      => $aggregation['query']
                ];
            }

            $documentSchema[ODM::D_COMPOSITIONS] = array_keys($document->getCompositions());

            ksort($documentSchema);
            $schema[$document->getClass()] = $documentSchema;
        }

        return $schema;
    }

    /**
     * Create all required collection indexes.
     *
     * @param ODM $odm ODM component is required as source for databases and collections.
     */
    public function createIndexes(ODM $odm)
    {
        foreach ($this->getDocumentSchemas() as $document)
        {
            if (!$indexes = $document->getIndexes())
            {
                continue;
            }

            $collection = $odm->db($document->getDatabase())->odmCollection(
                $document->getCollection()
            );

            foreach ($indexes as $index)
            {
                $options = [];
                if (isset($index[Document::INDEX_OPTIONS]))
                {
                    $options = $index[Document::INDEX_OPTIONS];
                    unset($index[Document::INDEX_OPTIONS]);
                }

                $collection->ensureIndex($index, $options);
            }
        }
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

        return [
            ODM::DEFINITION         => $classDefinition['type'],
            ODM::DEFINITION_OPTIONS => $classDefinition['options']
        ];
    }
}