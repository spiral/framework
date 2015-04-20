<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ODM;

use Spiral\Components\ODM\Exporters\DocumentationExporter;
use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Core\CoreException;

class ODM extends Component implements Container\InjectionManagerInterface
{
    /**
     * Will provide us helper method getInstance().
     */
    use Component\SingletonTrait, Component\ConfigurableTrait, Component\EventsTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = 'odm';

    /**
     * MongoDatabase class name.
     */
    const DATABASE = 'Spiral\Components\ODM\MongoDatabase';

    /**
     * Core component.
     *
     * @var CoreInterface
     */
    protected $core = null;

    /**
     * Loaded documents schema. Schema contains association between models and collections, children
     * chain, compiled default values and other presets can't be fetched in real time.
     *
     * @var array|null
     */
    protected $schema = null;

    /**
     * Mongo databases instances.
     *
     * @var MongoDatabase[]
     */
    protected $databases = array();

    /**
     * ODM component instance.
     *
     * @param CoreInterface $core
     * @throws CoreException
     */
    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
        $this->config = $core->loadConfig('odm');
    }

    /**
     * Get instance of MongoDatabase to handle connection, collections fetching and other operations.
     * Spiral MongoDatabase is layer at top of MongoClient ans MongoDB, it has all MongoDB features
     * plus ability to be injected.
     *
     * @param string $database Client ID.
     * @param array  $config   Connection options, only required for databases not listed in ODM config.
     * @return MongoDatabase
     * @throws ODMException
     */
    public function db($database = 'default', array $config = array())
    {
        if (isset($this->config['aliases'][$database]))
        {
            $database = $this->config['aliases'][$database];
        }

        if (isset($this->databases[$database]))
        {
            return $this->databases[$database];
        }

        if (empty($config))
        {
            if (!isset($this->config['databases'][$database]))
            {
                throw new ODMException(
                    "Unable to initiate mongo database, no presets for '{$database}' found."
                );
            }

            $config = $this->config['databases'][$database];
        }

        benchmark('odm::database', $database);

        $this->databases[$database] = Container::get(self::DATABASE, array(
            'name'   => $database,
            'config' => $config,
            'odm'    => $this
        ), null, true);

        benchmark('odm::database', $database);

        return $this->databases[$database];
    }

    /**
     * InjectionManager will receive requested class or interface reflection and reflection linked
     * to parameter in constructor or method used to declare dependency.
     *
     * This method can return pre-defined instance or create new one based on requested class, parameter
     * reflection can be used to dynamic class constructing, for example it can define database name
     * or config section should be used to construct requested instance.
     *
     * @param \ReflectionClass     $class
     * @param \ReflectionParameter $parameter
     * @return mixed
     */
    public static function resolveInjection(\ReflectionClass $class, \ReflectionParameter $parameter)
    {
        return self::getInstance()->db($parameter->getName());
    }

    /**
     * Get schema for specified document class or collection.
     *
     * @param string $item Document class or collection name (including database).
     * @return mixed
     */
    public function getSchema($item)
    {
        if ($this->schema === null)
        {
            $this->schema = $this->core->loadData('odmSchema');
        }

        if (!isset($this->schema[$item]))
        {
            $this->updateSchema();
        }

        return $this->schema[$item];
    }

    /**
     * Get ODM schema reader. Schema will detect all existed documents, collections, relationships
     * between them and will generate virtual documentation.
     *
     * @return SchemaBuilder
     */
    public function schemaBuilder()
    {
        return SchemaBuilder::make(array(
            'config' => $this->config
        ));
    }

    /**
     * Refresh ODM schema state, will reindex all found document models and render documentation for
     * them. This is slow method using Tokenizer, refreshSchema() should not be called by user request.
     *
     * @return SchemaBuilder
     */
    public function updateSchema()
    {
        $builder = $this->schemaBuilder();

        if (!empty($this->config['documentation']))
        {
            //Virtual ODM documentation to help IDE
            DocumentationExporter::make(compact('builder'))->render(
                $this->config['documentation']
            );
        }

        $this->schema = $this->event('schema', $builder->normalizeSchema());

        //Saving
        $this->core->saveData('odmSchema', $this->schema);

        return $builder;
    }

    /**
     * Create valid MongoId object based on string or id provided from client side, this methods can
     * be used as model filter as it will pass MongoId objects without any change.
     *
     * @param mixed $mongoID String or MongoId object.
     * @return \MongoId|null
     */
    public static function mongoID($mongoID)
    {
        if (empty($mongoID))
        {
            return null;
        }

        if (!is_object($mongoID))
        {
            //Old versions of mongo api does not throws exception on invalid mongo id (1.2.1)
            if (!is_string($mongoID) || !preg_match('/[0-9a-f]{24}/', $mongoID))
            {
                return null;
            }

            try
            {
                $mongoID = new \MongoId($mongoID);
            }
            catch (\Exception $exception)
            {
                return null;
            }
        }

        return $mongoID;
    }

    /**
     * Method will return class name selected based on class definition rules, rules defined in
     * Document class and can be LOGICAL or FIELDS based.
     *
     * @see Document::DEFINITION
     * @param mixed $fields     Document fields fetched from database.
     * @param mixed $definition Definition, can be string (one class) or array with options.
     * @return string
     */
    public static function defineClass($fields, $definition)
    {
        if (is_string($definition))
        {
            return $definition;
        }

        if ($definition[self::DEFINITION] == Document::DEFINITION_LOGICAL)
        {
            //Function based
            $definition = call_user_func($definition[self::DEFINITION_OPTIONS], $fields);
        }
        else
        {
            //Property based
            foreach ($definition[self::DEFINITION_OPTIONS] as $class => $field)
            {
                $definition = $class;
                if (array_key_exists($field, $fields))
                {
                    break;
                }
            }
        }

        return $definition;
    }

    /**
     * This is set of constants used in normalized ODM schema, you can use them to read already created
     * schema but they are useless besides normal development process.
     *
     * Class definition options.
     */
    const DEFINITION         = 0;
    const DEFINITION_OPTIONS = 1;

    /**
     * Normalized collection constants.
     */
    const C_NAME       = 0;
    const C_DB         = 1;
    const C_DEFINITION = 2;

    /**
     * Normalized document constants.
     */
    const D_COLLECTION   = 0;
    const D_DB           = 1;
    const D_DEFAULTS     = 2;
    const D_HIDDEN       = 3;
    const D_SECURED      = 4;
    const D_FILLABLE     = 5;
    const D_MUTATORS     = 6;
    const D_VALIDATES    = 7;
    const D_MESSAGES     = 8;
    const D_AGGREGATIONS = 9;
    const D_COMPOSITIONS = 10;

    /**
     * Normalized aggregation.
     */
    const AGR_TYPE  = 0;
    const AGR_QUERY = 1;

    /**
     * Matched to D_COLLECTION and D_DB to use in Document::odmCollection() method.
     */
    const AGR_COLLECTION = 0;
    const AGR_DB         = 1;

    /**
     * Normalized composition.
     */
    const CMP_TYPE       = 0;
    const CMP_DEFINITION = 1;
    const CMP_ONE        = 0x111;
    const CMP_MANY       = 0x222;
}