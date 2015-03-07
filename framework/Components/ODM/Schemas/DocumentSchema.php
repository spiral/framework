<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ODM\Schemas;

use Spiral\Components\Localization\I18nManager;
use Spiral\Components\ODM\Document;
use Spiral\Components\ODM\ODM;
use Spiral\Components\ODM\ODMAccessor;
use Spiral\Components\ODM\ODMException;
use Spiral\Components\ODM\SchemaReader;
use Spiral\Core\Component;
use Spiral\Support\Models\DataEntity;

class DocumentSchema extends Component
{
    /**
     * Document model class name.
     *
     * @var string
     */
    protected $class = '';

    /**
     * Parent ODM schema holds all other documents.
     *
     * @invisible
     * @var SchemaReader
     */
    protected $odmSchema = null;

    /**
     * Document model reflection.
     *
     * @var null|\ReflectionClass
     */
    protected $reflection = null;

    /**
     * Cache to speed up schema building.
     *
     * @var array
     */
    protected $propertiesCache = array();

    /**
     * New DocumentSchema instance, document schema responsible for fetching schema, defaults
     * and filters from Document models.
     *
     * @param string       $class     Class name.
     * @param SchemaReader $odmSchema Parent ODM schema (all other documents).
     */
    public function __construct($class, SchemaReader $odmSchema)
    {
        $this->class = $class;
        $this->odmSchema = $odmSchema;
        $this->reflection = new \ReflectionClass($class);
    }

    /**
     * Checks if class is abstract.
     *
     * @return bool
     */
    public function isAbstract()
    {
        return $this->reflection->isAbstract();
    }

    /**
     * Document full class name.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Document namespace. Both start and end namespace separators will be removed, to add start separator (absolute)
     * namespace use method parameter "absolute".
     *
     * @param bool $absolute \\ will be prepended to namespace if true, disabled by default.
     * @return string
     */
    public function getNamespace($absolute = false)
    {
        return ($absolute ? '\\' : '') . trim($this->reflection->getNamespaceName(), '\\');
    }

    /**
     * Document class name without included namespace.
     *
     * @return string
     */
    public function shortName()
    {
        $names = explode('\\', $this->class);

        return end($names);
    }

    /**
     * Reading default model property value, will read "protected" and "private" properties.
     *
     * @param string $property Property name.
     * @param bool   $merge    If true value will be merged with all parent declarations.
     * @return mixed
     */
    protected function property($property, $merge = false)
    {
        if (isset($this->propertiesCache[$property]))
        {
            return $this->propertiesCache[$property];
        }

        $defaults = $this->reflection->getDefaultProperties();
        if (isset($defaults[$property]))
        {
            $value = $defaults[$property];
        }
        else
        {
            return null;
        }

        if ($merge && ($this->reflection->getParentClass()->getName() != SchemaReader::DOCUMENT))
        {
            $parentClass = $this->reflection->getParentClass()->getName();
            $value = array_merge($this->odmSchema->getDocument($parentClass)->property($property, true), $value);
        }

        return $this->propertiesCache[$property] = call_user_func(
            array($this->getClass(), 'describeProperty'),
            $this,
            $property,
            $value
        );
    }

    /**
     * Parent document class, null if model extended directly from Document class.
     *
     * @return null|string
     */
    public function getParent()
    {
        $parentClass = $this->reflection->getParentClass()->getName();

        return $parentClass != SchemaReader::DOCUMENT ? $parentClass : null;
    }

    /**
     * Get collection name associated with document model.
     *
     * @return mixed
     */
    public function getCollection()
    {
        return $this->property('collection');
    }

    /**
     * Get database model data should be stored in.
     *
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->property('database');
    }

    /**
     * Get document declared schema (merged with parent model(s) values).
     *
     * @return array
     */
    public function getSchema()
    {
        //Reading schema as property to inherit all values
        return $this->property('schema', true);
    }

    /**
     * Document embedded fields, including compositions.
     *
     * @return array
     */
    public function getFields()
    {
        //We should select only embedded fields, no aggregations
        $schema = $this->getSchema();

        $fields = array();
        foreach ($schema as $field => $type)
        {
            if (is_array($type) && ((array_key_exists(Document::MANY, $type) || array_key_exists(Document::ONE, $type))))
            {
                //Aggregation
                continue;
            }

            $fields[$field] = $type;
        }

        return $fields;
    }

    /**
     * Find all field mutators.
     *
     * @return mixed
     */
    public function getMutators()
    {
        $mutators = array(
            'getter'   => array(),
            'setter'   => array(),
            'accessor' => array()
        );

        foreach ($this->property('getters', true) as $field => $filter)
        {
            $mutators['getter'][$field] = $filter;
        }

        foreach ($this->property('setters', true) as $field => $filter)
        {
            $mutators['setter'][$field] = $filter;
        }

        foreach ($this->property('accessors', true) as $field => $filter)
        {
            $mutators['accessor'][$field] = $filter;
        }

        //Default values.
        foreach ($this->getFields() as $field => $type)
        {
            $resolved = array();

            if (is_array($type) && is_scalar($type[0]) && $filter = $this->odmSchema->findMutator($field . '::' . $type[0]))
            {
                $resolved += $filter;
            }
            elseif (is_array($type) && $filter = $this->odmSchema->findMutator('array'))
            {
                $resolved += $filter;
            }
            elseif (!is_array($type) && $filter = $this->odmSchema->findMutator($type))
            {
                $resolved += $filter;
            }

            if (isset($resolved['accessor']))
            {
                //Ensuring type for accessor
                $resolved['accessor'] = array($resolved['accessor'], is_array($type) ? $type[0] : $type);
            }

            foreach ($resolved as $mutator => $filter)
            {
                if (!array_key_exists($field, $mutators[$mutator]))
                {
                    $mutators[$mutator][$field] = $filter;
                }
            }
        }

        //Mounting composition accessors
        foreach ($this->getCompositions() as $field => $composition)
        {
            //Composition::ONE has to be resolved little bit different way due model inheritance
            $mutators['accessor'][$field] = array(
                $composition['type'] == ODM::CMP_MANY ? SchemaReader::COMPOSITOR : ODM::CMP_ONE,
                $composition['classDefinition']
            );
        }

        return $mutators;
    }

    /**
     * Getting all secured fields.
     *
     * @return array
     */
    public function getSecured()
    {
        return $this->property('secured', true);
    }

    /**
     * Getting all assignable fields.
     *
     * @return array
     */
    public function getAssignable()
    {
        return $this->property('assignable', true);
    }

    /**
     * Getting all hidden fields.
     *
     * @return array
     */
    public function getHidden()
    {
        return $this->property('hidden', true);
    }

    /**
     * Get document get filters (merged with parent model(s) values).
     *
     * @return array
     */
    public function getGetters()
    {
        return $this->getMutators()['getter'];
    }

    /**
     * Get document set filters (merged with parent model(s) values).
     *
     * @return array
     */
    public function getSetters()
    {
        return $this->getMutators()['setter'];
    }

    /**
     * Get document field accessors, this method will automatically create accessors for compositions.
     *
     * @return array
     */
    public function getAccessors()
    {
        return $this->getMutators()['accessor'];
    }

    /**
     * Get document default values (merged with parent model(s) values). Default values will be passed thought model filters,
     * this will help us to ensure that field will always have desired type.
     *
     * @return array
     */
    public function getDefaults()
    {
        $defaults = $this->property('defaults', true);

        foreach ($this->getCompositions() as $field => $composition)
        {
            if ($composition['type'] == ODM::CMP_ONE)
            {
                $defaults[$field] = $this->odmSchema->getDocument($composition['class'])->getDefaults();
            }
        }

        $setters = $this->getSetters();
        $accessors = $this->getAccessors();
        foreach ($this->getFields() as $field => $type)
        {
            $default = is_array($type) ? array() : null;

            if (array_key_exists($field, $defaults))
            {
                $default = $defaults[$field];
            }

            if (isset($setters[$field]))
            {
                $filter = $setters[$field];
                if (is_string($filter) && isset(DataEntity::$mutatorAliases[$filter]))
                {
                    $filter = DataEntity::$mutatorAliases[$filter];
                }

                //Applying filter to default value
                try
                {
                    $default = call_user_func($filter, $default);
                }
                catch (\ErrorException $exception)
                {
                    $default = null;
                }
            }

            if (isset($accessors[$field]))
            {
                $accessor = $accessors[$field];

                $options = null;
                if (is_array($accessor))
                {
                    list($accessor, $options) = $accessor;
                }

                if ($accessor != ODM::CMP_ONE)
                {
                    //Not an accessor but composited class
                    $accessor = new $accessor($default, null, $options);

                    if ($accessor instanceof ODMAccessor)
                    {
                        $default = $accessor->defaultValue();
                    }
                }
            }

            $defaults[$field] = $default;
        }

        return $defaults;
    }

    /**
     * Get all document validation rules (merged with parent model(s) values).
     *
     * @return array
     */
    public function getValidates()
    {
        return $this->property('validates', true);
    }

    /**
     * Get error messages localization sources. This is required to correctly localize model errors without overlaps.
     *
     * @return array
     */
    public function getMessages()
    {
        $validates = array();
        $reflection = $this->reflection;
        while ($reflection->getName() != SchemaReader::DOCUMENT)
        {
            //Validation messages
            if (!empty($reflection->getDefaultProperties()['validates']))
            {
                $validates[$reflection->getName()] = $reflection->getDefaultProperties()['validates'];
            }

            $reflection = $reflection->getParentClass();
        }

        $messages = array();
        foreach (array_reverse($validates) as $parent => $validates)
        {
            foreach ($validates as $field => $rules)
            {
                foreach ($rules as $rule)
                {
                    $message = '';
                    if (isset($rule['message']))
                    {
                        $message = $rule['message'];
                    }
                    elseif (isset($rule['error']))
                    {
                        $message = $rule['error'];
                    }
                    if (substr($message, 0, 2) == I18nManager::I18N_PREFIX && substr($message, -2) == I18nManager::I18N_POSTFIX)
                    {
                        //Only I18N messages
                        if ($message && !isset($errorMessages[$message]))
                        {
                            $messages[$message] = $parent;
                        }
                    }
                }
            }
        }

        return $messages;
    }

    /**
     * All methods declared in document. Method will include information about parameters, return type, static declaration
     * and access level.
     *
     * @return MethodSchema[]
     */
    public function getMethods()
    {
        $methods = array();

        foreach ($this->reflection->getMethods() as $method)
        {
            if ($method->getDeclaringClass() != $this->reflection)
            {
                continue;
            }

            $methods[] = MethodSchema::make(array('reflection' => $method));
        }

        return $methods;
    }

    /**
     * Get all document compositions.
     *
     * @return array
     */
    public function getCompositions()
    {
        $fields = $this->getFields();

        $compositions = array();
        foreach ($fields as $field => $type)
        {
            if (is_string($type) && $foreignDocument = $this->odmSchema->getDocument($type))
            {
                $compositions[$field] = array(
                    'type'            => ODM::CMP_ONE,
                    'class'           => $type,
                    'classDefinition' => $foreignDocument->classDefinition()
                );
                continue;
            }

            //Class name should be stored in first array argument
            if (!is_array($type))
            {
                try
                {
                    if (class_exists($type))
                    {
                        $reflection = new \ReflectionClass($type);
                        if ($reflection->implementsInterface(SchemaReader::COMPOSITABLE))
                        {
                            $compositions[$field] = array(
                                'type'            => ODM::CMP_ONE,
                                'class'           => $type,
                                'classDefinition' => $type
                            );
                        }
                    }
                }
                catch (\Exception $exception)
                {
                    //Ignoring
                }

                continue;
            }

            $class = $type[0];
            if (is_string($class) && $foreignDocument = $this->odmSchema->getDocument($class))
            {
                //Rename type to represent real model name
                $compositions[$field] = array(
                    'type'            => ODM::CMP_MANY,
                    'class'           => $class,
                    'classDefinition' => $foreignDocument->classDefinition()
                );
            }
        }

        return $compositions;
    }

    /**
     * Get field references to external documents (aggregations).
     *
     * @return array
     * @throws ODMException
     */
    public function getAggregations()
    {
        $schema = $this->getSchema();

        $aggregations = array();
        foreach ($schema as $field => $options)
        {
            if (!is_array($options) || (!array_key_exists(Document::MANY, $options) && !array_key_exists(Document::ONE, $options)))
            {
                //Not aggregation
                continue;
            }

            //Class to be aggregated
            $class = isset($options[Document::MANY]) ? $options[Document::MANY] : $options[Document::ONE];

            if (!$externalDocument = $this->odmSchema->getDocument($class))
            {
                throw new ODMException("Unable to build aggregation {$this->class}.{$field}, no such document '{$class}'.");
            }

            if (!$externalDocument->getCollection())
            {
                throw new ODMException("Unable to build aggregation {$this->class}.{$field}, document '{$class}' does not have any collection.");
            }

            $aggregations[$field] = array(
                'type'       => isset($options[Document::ONE]) ? Document::ONE : Document::MANY,
                'class'      => $class,
                'collection' => $externalDocument->getCollection(),
                'database'   => $externalDocument->getDatabase(),
                'query'      => array_pop($options)
            );
        }

        return $aggregations;
    }

    /**
     * Get all possible children (sub models) for this document.
     *
     * Example:
     * class A
     * class B extends A
     * class D extends A
     * class E extends D
     *
     * result: B,D,E
     *
     * @return array
     */
    public function getChildren()
    {
        $result = array();
        foreach ($this->odmSchema->getDocuments() as $schema)
        {
            if ($schema->reflection->isSubclassOf($this->class))
            {
                $result[] = $schema->reflection->getName();
            }
        }

        return $result;
    }

    /**
     * Class name of first document used to create current model. Basically this is first class in extending chain.
     *
     * @param bool $hasCollection Only document with defined collection.
     * @return string
     */
    public function primaryClass($hasCollection = false)
    {
        $reflection = $this->reflection;

        while ($reflection->getParentClass()->getName() != SchemaReader::DOCUMENT)
        {
            if ($hasCollection && !$this->odmSchema->getDocument($reflection->getParentClass()->getName())->getCollection())
            {
                break;
            }

            $reflection = $reflection->getParentClass();
        }

        return $reflection->getName();
    }

    /**
     * Document schema of first document used to create current model. Basically this is first class in extending chain.
     *
     * @param bool $hasCollection Only document with defined collection.
     * @return DocumentSchema
     */
    public function primaryDocument($hasCollection = false)
    {
        return $this->odmSchema->getDocument($this->primaryClass($hasCollection));
    }

    /**
     * How to define valid class declaration based on set of fields fetched from collection, default way is "FIELDS",
     * this method will define set of unique fields existed in every class. Second option is to define method to resolve
     * class declaration "LOGICAL".
     *
     * @return mixed
     * @throws ODMException
     */
    public function classDefinition()
    {
        $classes = array();
        foreach ($this->odmSchema->getDocuments() as $documentSchema)
        {
            if ($documentSchema->reflection->isSubclassOf($this->class) && !$documentSchema->reflection->isAbstract())
            {
                $classes[] = $documentSchema->class;
            }
        }

        $classes[] = $this->class;

        if (count($classes) == 1)
        {
            //No sub classes
            return $this->class;
        }

        if ($this->reflection->getConstant('DEFINITION') == Document::DEFINITION_LOGICAL)
        {
            return array(
                'type'    => Document::DEFINITION_LOGICAL,
                'options' => array($this->primaryClass(), 'defineClass')
            );
        }
        else
        {
            $defineClass = array(
                'type'    => Document::DEFINITION_FIELDS,
                'options' => array()
            );

            /**
             * We should order classes by inheritance levels. Primary model should go last.
             */
            uasort($classes, function ($classA, $classB)
            {
                return (new \ReflectionClass($classA))->isSubclassOf($classB) ? 1 : -1;
            });

            //Populating model fields
            $classes = array_flip($classes);

            //Array of fields can be found in any model
            $commonFields = array();

            foreach ($classes as $class => &$fields)
            {
                $fields = $this->odmSchema->getDocument($class)->getFields();

                if (!$fields)
                {
                    return null;
                }

                if (!$commonFields)
                {
                    $commonFields = $fields;
                }
                else
                {
                    foreach ($fields as $field => $type)
                    {
                        if (isset($commonFields[$field]))
                        {
                            unset($fields[$field]);
                        }
                        else
                        {
                            //Remove aey for all inherited models
                            $commonFields[$field] = true;
                        }
                    }
                }

                if (!$fields)
                {
                    throw new ODMException("Unable to use class detection (property based) for document '{$class}', no unique fields found.");
                }

                reset($fields);
                $defineClass['options'][$class] = key($fields);
                unset($fields);
            }
        }

        //Back order
        $defineClass['options'] = array_reverse($defineClass['options']);

        return $defineClass;
    }
}