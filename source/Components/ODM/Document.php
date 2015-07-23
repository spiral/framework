<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ODM;

use Spiral\Components\I18n\Translator;
use Spiral\Core\Events\EventDispatcher;
use Spiral\Support\Models\AccessorInterface;
use Spiral\Support\Models\DatabaseEntityInterface;
use Spiral\Support\Models\DataEntity;
use Spiral\Support\Validation\Validator;

abstract class Document extends DataEntity implements CompositableInterface, DatabaseEntityInterface
{
    /**
     * We are going to inherit parent validation, we have to let i18n indexer know to collect both
     * local and parent messages under one bundle.
     */
    const I18N_INHERIT_MESSAGES = true;

    /**
     * Model specific constant to indicate that model has to be validated while saving. You still can
     * change this behaviour manually by providing argument to save method.
     */
    const FORCE_VALIDATION = true;

    /**
     * Helper constant to identify atomic SET operations.
     */
    const ATOMIC_SET = '$set';

    /**
     * System will define which class should be used for document representation based on unique class
     * fields.
     *
     * Example:
     * class A: _id, name, address
     * class B extends A: _id, name, address, email
     *
     * class B will be used to represent all documents with existed email field.
     */
    const DEFINITION_FIELDS = 1;

    /**
     * System will define which class should be used for document representation based on static
     * method result.
     *
     * Document::defineClass(items)
     *
     * Example:
     * class A: _id, name, type (a)
     * class B extends A: _id, name, type (b)
     * class C extends B: _id, name, type (c)
     *
     * Static method in class A should return A, B or C based on some field values.
     */
    const DEFINITION_LOGICAL = 2;

    /**
     * How to define valid class declaration based on set of fields fetched from collection, default
     * way is "DEFINITION_FIELDS", this method will define set of unique fields existed in every class.
     * Second option is to define method to resolve class declaration "DEFINITION_LOGICAL".
     */
    const DEFINITION = self::DEFINITION_FIELDS;

    /**
     * Aggregation types. Use appropriate type to declare reference to one or to many.
     *
     * Example:
     * 'items' => [self::MANY => 'Models\Database\Item', [
     *      'parentID' => 'key::_id'
     * ]]
     */
    const MANY = 778;
    const ONE  = 899;

    /**
     * Automatically convert "_id" to "id".
     */
    const REMOVE_ID_UNDERSCORE = true;

    /**
     * Chunk to hold index options.
     */
    const INDEX_OPTIONS = '@options';

    /**
     * Already fetched schemas from ODM.
     *
     * @var array
     */
    protected static $schemaCache = [];

    /**
     * ODM component.
     *
     * @var ODM
     */
    protected $odm = null;

    /**
     * Parent object (composition owner).
     *
     * @invisible
     * @var CompositableInterface|Document
     */
    protected $parent = null;

    /**
     * Collection name where document should be stored into. Collection will be automatically created
     * on first document save.
     *
     * @var string
     */
    protected $collection = null;

    /**
     * Database name/id where document related collection located in. By default default database
     * will be used.
     *
     * @var string
     */
    protected $database = 'default';

    /**
     * List of secured fields, such fields can not be set using setFields() method (only directly).
     *
     * @var array
     */
    protected $secured = ['_id'];

    /**
     * Object fields, sub objects and relationships.
     *
     * Example:
     *
     * _id          => MongoId       //Column, expected type MongoId
     * value        => string        //Column, expected type string
     * values       => [string]      //Column, array of strings, will be represented using ScalarArray
     *
     * Compositions:
     * subDocument  => DocumentClass        //Structure represented by document type DocumentClass
     * subDocuments => [DocumentClass]      //Array of documents type DocumentClass
     *
     * Aggregations:
     * relationship => [self::MANY => DocumentClass, [
     *          someID => key::_id, key => value...
     * ]] //Reference to many DocumentClass
     *
     * relationship => [self::ONE => DocumentClass, [
     *          someID => key::_id, key => value...
     * ]] //Reference to one DocumentClass
     *
     * Schema will be extended in child document classes, additionally ODM will set some default
     * filters based on values in ODM configuration and field type.
     *
     * @var array
     */
    protected $schema = [];

    /**
     * Default values associated with document fields. Every default value will be passed thought
     * appropriate filter to ensure that value type is strictly set.
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * Set of indexes to be created for associated collection. Use self::INDEX_OPTIONS or @options
     * for additional parameters.
     *
     * Example:
     *  protected $indexes = [
     *      ['email' => 1, '@options' => ['unique' => true]],
     *      ['name' => 1]
     * ];
     *
     * @link http://php.net/manual/en/mongocollection.ensureindex.php
     * @var array
     */
    protected $indexes = [];

    /**
     * Documents marked with solid state flag will be saved entirely without generating separate
     * atomic operations for each field, instead one big set operation will be created. Your atomic()
     * calls with be applied to document data but will not be forwarded to collection.
     *
     * @var bool
     */
    protected $solidState = false;

    /**
     * List of updated fields associated with their original values.
     *
     * @var array
     */
    protected $updates = [];

    /**
     * Set of atomic operation has to be performed to save document into database. Atomic operation
     * can not be generated if document in solid state. Some atomic operation (set) will be created
     * automatically while changing document fields.
     *
     * @var array
     */
    protected $atomics = [];

    /**
     * Create new Document instance, schema will be automatically loaded and cached. Note that fields
     * provided in constructor will not be filtered, you have to use create() method for it, however
     * input fields will be merged with default values to ensure that model is always in correct shape.
     *
     * @param array                 $data   Document fields, filters will not be applied for this fields.
     * @param CompositableInterface $parent Parent document or compositor.
     * @param mixed                 $options
     * @param ODM                   $odm    ODM component, will be received from container if not
     *                                      provided.
     */
    public function __construct($data = [], $parent = null, $options = null, ODM $odm = null)
    {
        $this->parent = $parent;
        $this->odm = !empty($odm) ? $odm : ODM::getInstance();

        if (!isset(self::$schemaCache[$class = get_class($this)]))
        {
            static::initialize();
            self::$schemaCache[$class] = $this->odm->getSchema(get_class($this));
        }

        //Prepared document schema
        $this->schema = self::$schemaCache[$class];

        //Forcing default values
        if (!empty($this->schema[ODM::D_DEFAULTS]))
        {
            $this->fields = $data
                ? array_replace_recursive(
                    $this->schema[ODM::D_DEFAULTS],
                    is_array($data) ? $data : []
                )
                : $this->schema[ODM::D_DEFAULTS];
        }

        if ((!$this->primaryKey() && empty($this->parent)) || !is_array($data))
        {
            $this->solidState(true)->validationRequired = true;
        }
    }

    /**
     * Define class name should be used to represent fields fetched from Mongo collection. This method
     * will be called if Document::DEFINITION constant equal to Document::DEFINITION_LOGICAL.
     *
     * @param array $fields
     * @return string
     */
    public static function defineClass(array $fields)
    {
        //Nothing
    }

    /**
     * Change document solid state flag value. Documents marked with solid state flag will be saved
     * entirely without generating separate atomic operations for each field, instead one big set
     * operation will be called. Atomic operations functionality will be disabled.
     *
     * @param bool $solidState  Solid state flag value.
     * @param bool $forceUpdate Mark all fields as changed to force update later.
     * @return static
     */
    public function solidState($solidState, $forceUpdate = false)
    {
        $this->solidState = $solidState;

        if ($forceUpdate)
        {
            $this->updates = $this->schema[ODM::D_DEFAULTS];
        }

        return $this;
    }

    /**
     * Get document primary key (_id) value. This value can be used to identify if model loaded from
     * databases or just created.
     *
     * @return \MongoId
     */
    public function primaryKey()
    {
        return isset($this->fields['_id']) ? $this->fields['_id'] : null;
    }

    /**
     * Is model were fetched from databases or recently created? Usually checks primary key value.
     *
     * @return bool
     */
    public function isLoaded()
    {
        return (bool)$this->primaryKey();
    }

    /**
     * True is document embedded to other document or part of composition.
     *
     * @return bool
     */
    public function isEmbedded()
    {
        return (bool)$this->parent;
    }

    /**
     * Get mutator for specified field. Setters, getters and accessors can be retrieved using this
     * method.
     *
     * @param string $field   Field name.
     * @param string $mutator Mutator type (setters, getters, accessors).
     * @return mixed|null
     */
    protected function getMutator($field, $mutator)
    {
        if (isset($this->schema[ODM::D_MUTATORS][$mutator][$field]))
        {
            $mutator = $this->schema[ODM::D_MUTATORS][$mutator][$field];

            if (is_string($mutator) && isset(self::$mutatorAliases[$mutator]))
            {
                return self::$mutatorAliases[$mutator];
            }

            return $mutator;
        }

        return null;
    }

    /**
     * Copy Compositable to embed into specified parent. Documents with already set parent will return
     * copy of themselves, in other scenario document will return itself.
     *
     * @param CompositableInterface $parent Parent ODMCompositable object should be copied or prepared
     *                                      for.
     * @return static
     */
    public function embed($parent)
    {
        if (empty($this->parent))
        {
            $this->parent = $parent;

            return $this->solidState(true, true);
        }

        if ($parent === $this->parent)
        {
            return $this;
        }

        return (new static($this->serializeData(), $parent))->solidState(true, true);
    }

    /**
     * Get accessor instance.
     *
     * @param mixed  $value    Value to mock up.
     * @param string $accessor Accessor definition (can be array).
     * @return AccessorInterface
     */
    protected function defineAccessor($value, $accessor)
    {
        $options = null;
        if (is_array($accessor))
        {
            list($accessor, $options) = $accessor;
        }

        if ($accessor == ODM::CMP_ONE)
        {
            //Not an accessor by composited class
            $accessor = $this->odm->defineClass($value, $options);
        }

        return new $accessor($value, $this, $options, $this->odm);
    }

    /**
     * Update accessor mocked data.
     *
     * @param mixed $data
     * @return static
     */
    public function setData($data)
    {
        return $this->setFields($data);
    }

    /**
     * Check if field assignable.
     *
     * @param string $field
     * @return bool
     */
    protected function isFillable($field)
    {
        //Better replace it with isset later
        return !in_array($field, $this->schema[ODM::D_SECURED]) &&
        !(
            $this->schema[ODM::D_FILLABLE]
            && !in_array($field, $this->schema[ODM::D_FILLABLE])
        );
    }

    /**
     * Get all non secured model fields. ODM will automatically convert "_id" to "id" and convert all
     * MongoId and MongoDates to scalar representations.
     *
     * @return array
     */
    public function publicFields()
    {
        $result = [];

        foreach ($this->fields as $field => $value)
        {
            //Better replace it with isset later
            if (in_array($field, $this->schema[ODM::D_HIDDEN]))
            {
                continue;
            }

            $value = $this->getField($field);

            //Better replace it with isset later
            if (in_array($field, $this->schema[ODM::D_COMPOSITIONS]))
            {
                //Letting document to do the rest (expecting to be document or compositor)
                $result[$field] = $value->publicFields();
                continue;
            }

            if ($value instanceof \MongoId)
            {
                $value = (string)$value;
            }

            if (is_array($value))
            {
                array_walk_recursive($value, function (&$value)
                {
                    if ($value instanceof \MongoId)
                    {
                        $value = (string)$value;
                    }
                });
            }

            if (static::REMOVE_ID_UNDERSCORE && $field == '_id')
            {
                $field = 'id';
            }

            $result[$field] = $value;
        }

        return $this->event('publicFields', $result);
    }

    /**
     * Get related document/documents.
     *
     * @param string $offset
     * @param array  $arguments Additional query can be provided as first argument.
     * @return Collection|Document|Document[]
     * @throws ODMException
     */
    public function __call($offset, array $arguments)
    {
        if (!isset($this->schema[ODM::D_AGGREGATIONS][$offset]))
        {
            throw new ODMException(
                "Unable to call " . get_class($this) . "->{$offset}(), no such function."
            );
        }

        $aggregation = $this->schema[ODM::D_AGGREGATIONS][$offset];

        //Query preparations
        $query = $this->prepareQuery($aggregation[ODM::AGR_QUERY]);

        if (isset($arguments[0]) && is_array($arguments[0]))
        {
            $query = array_merge($query, $arguments[0]);
        }

        $collection = static::odmCollection($this->odm, $aggregation)->query($query);
        if ($aggregation[ODM::AGR_TYPE] == self::ONE)
        {
            return $collection->findOne();
        }

        return $collection;
    }

    /**
     * Prepare aggregation query, all "key::name" usages will be replaced with real field value.
     *
     * @param array $query
     * @return array
     */
    protected function prepareQuery(array $query)
    {
        $fields = $this->fields;
        array_walk_recursive($query, function (&$value) use ($fields)
        {
            if (strpos($value, 'key::') === 0)
            {
                $value = $fields[substr($value, 5)];
                if ($value instanceof CompositableInterface)
                {
                    $value = $value->serializeData();
                }
            }
        });

        return $query;
    }

    /**
     * Set value to one of field. Setter filter can be disabled by providing last argument.
     *
     * @param string $name   Field name.
     * @param mixed  $value  Value to set.
     * @param bool   $filter If false no filter will be applied.
     */
    public function setField($name, $value, $filter = true)
    {
        $original = isset($this->fields[$name]) ? $this->fields[$name] : null;
        parent::setField($name, $value, $filter);

        if (!array_key_exists($name, $this->updates))
        {
            $this->updates[$name] = $original instanceof AccessorInterface
                ? $original->serializeData()
                : $original;
        }
    }

    /**
     * Offset to unset.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset The offset to unset.
     */
    public function __unset($offset)
    {
        if (!array_key_exists($offset, $this->updates))
        {
            //Letting document know that field value changed, but without overwriting previous change
            $this->updates[$offset] = isset($this->schema[ODM::D_DEFAULTS][$offset])
                ? $this->schema[ODM::D_DEFAULTS][$offset]
                : null;
        }

        $this->fields[$offset] = null;
        if (isset($this->schema[ODM::D_DEFAULTS][$offset]))
        {
            $this->fields[$offset] = $this->schema[ODM::D_DEFAULTS][$offset];
        }
    }

    /**
     * Alias for atomic operation $set. Attention, this operation is not identical to setField() method,
     * it performs low level operation and can be used only for simple fields.
     *
     * @param string $field
     * @param mixed  $value
     * @return static
     * @throws ODMException
     */
    public function set($field, $value)
    {
        if ($this->hasUpdates($field, true))
        {
            throw new ODMException("Unable to apply multiple atomic operation to field '{$field}'.");
        }

        $this->atomics['$set'][$field] = $value;
        $this->fields[$field] = $value;

        return $this;
    }

    /**
     * Alias for atomic operation $inc.
     *
     * @param string $field
     * @param string $value
     * @return static
     * @throws ODMException
     */
    public function inc($field, $value)
    {
        if ($this->hasUpdates($field, true) && !isset($this->atomics['$inc'][$field]))
        {
            throw new ODMException("Unable to apply multiple atomic operation to field '{$field}'.");
        }

        if (!isset($this->atomics['$inc'][$field]))
        {
            $this->atomics['$inc'][$field] = 0;
        }

        $this->atomics['$inc'][$field] += $value;
        $this->fields[$field] += $value;

        return $this;
    }

    /**
     * Get generated and manually set document/object atomic updates.
     *
     * @param string $container Name of field or index where document stored into.
     * @return array
     */
    public function buildAtomics($container = '')
    {
        if (!$this->hasUpdates() && !$this->solidState)
        {
            return [];
        }

        if ($this->solidState)
        {
            if (!empty($container))
            {
                return [self::ATOMIC_SET => [$container => $this->getFields()]];
            }

            $atomics = [self::ATOMIC_SET => $this->getFields()];
            unset($atomics[self::ATOMIC_SET]['_id']);

            return $atomics;
        }

        if (empty($container))
        {
            $atomics = $this->atomics;
        }
        else
        {
            $atomics = [];

            foreach ($this->atomics as $atomic => $fields)
            {
                foreach ($fields as $field => $value)
                {
                    $atomics[$atomic][$container . '.' . $field] = $value;
                }
            }
        }

        foreach ($this->fields as $field => $value)
        {
            if ($field == '_id')
            {
                continue;
            }

            if ($value instanceof CompositableInterface)
            {
                $atomics = array_merge_recursive(
                    $atomics,
                    $value->buildAtomics(
                        ($container ? $container . '.' : '') . $field
                    )
                );

                continue;
            }

            foreach ($atomics as $atomic => $operations)
            {
                if (array_key_exists($field, $operations) && $atomic != self::ATOMIC_SET)
                {
                    //Property already changed by atomic operation
                    continue;
                }
            }

            if (array_key_exists($field, $this->updates))
            {
                //Generating set operation
                $atomics[self::ATOMIC_SET][($container ? $container . '.' : '') . $field] = $value;
            }
        }

        return $atomics;
    }

    /**
     * Check if document or specific field is updated.
     *
     * @param string $field
     * @param bool   $atomicsOnly Only atomic updates will be checked.
     * @return bool
     */
    public function hasUpdates($field = null, $atomicsOnly = false)
    {
        if (empty($field))
        {
            if (!empty($this->updates) || !empty($this->atomics))
            {
                return true;
            }

            foreach ($this->fields as $field => $value)
            {
                if ($value instanceof CompositableInterface && $value->hasUpdates())
                {
                    return true;
                }
            }

            return false;
        }

        foreach ($this->atomics as $operations)
        {
            if (array_key_exists($field, $operations))
            {
                //Property already changed by atomic operation
                return true;
            }
        }

        if ($atomicsOnly)
        {
            return false;
        }

        if (array_key_exists($field, $this->updates))
        {
            return true;
        }

        return false;
    }

    /**
     * Mark object as successfully updated and flush all existed atomic operations and updates.
     */
    public function flushUpdates()
    {
        $this->updates = $this->atomics = [];

        foreach ($this->fields as $value)
        {
            if ($value instanceof CompositableInterface)
            {
                $value->flushUpdates();
            }
        }
    }

    /**
     * Validator instance associated with model, will be response for validations of validation errors.
     * Model related error localization should happen in model itself.
     *
     * @return Validator
     */
    public function getValidator()
    {
        if (!empty($this->validator))
        {
            //Refreshing data
            return $this->validator->setData($this->fields);
        }

        return $this->validator = Validator::make([
            'data'      => $this->fields,
            'validates' => $this->schema[ODM::D_VALIDATES]
        ]);
    }

    /**
     * Validating model data using validation rules, all errors will be stored in model errors array.
     * Errors will not be erased between function calls.
     *
     * @return bool
     */
    protected function validate()
    {
        $validationRequired = $this->validationRequired;
        parent::validate();

        //Validating all compositions
        foreach ($this->schema[ODM::D_COMPOSITIONS] as $field)
        {
            $compositor = $this->getField($field);

            //Forcing validation (we expecting compositors to be only Documents and Compositors)
            $validationRequired && $compositor->requestValidation();
            if (!$compositor->isValid())
            {
                $this->errors[$field] = $compositor->getErrors();
            }
        }

        return empty($this->errors);
    }

    /**
     * Get all validation errors with applied localization using i18n component (if specified), any
     * error message can be localized by using [[ ]] around it. Data will be automatically validated
     * while calling this method (if not validated before).
     *
     * @param bool $reset Remove all model messages and reset validation, false by default.
     * @return array
     */
    public function getErrors($reset = false)
    {
        $this->validate();
        $errors = [];
        foreach ($this->errors as $field => $error)
        {
            if (
                is_string($error)
                && substr($error, 0, 2) == Translator::I18N_PREFIX
                && substr($error, -2) == Translator::I18N_POSTFIX
            )
            {
                $error = $this->i18nMessage($error);
            }

            $errors[$field] = $error;
        }

        if ($reset)
        {
            $this->errors = [];
        }

        return $errors;
    }

    /**
     * Get ODM collection associated with specified document.
     *
     * @param ODM   $odm    ODM component, will be received from Container if not specified.
     * @param array $schema Forced document schema.
     * @return Collection
     */
    public static function odmCollection(ODM $odm = null, array $schema = [])
    {
        $odm = !empty($odm) ? $odm : ODM::getInstance();
        $schema = !empty($schema) ? $schema : $odm->getSchema(get_called_class());

        static::initialize();

        return new Collection($odm, $schema[ODM::D_DB], $schema[ODM::D_COLLECTION]);
    }

    /**
     * Save document and all nested data to ODM collection. Document has to be valid to be saved, in
     * other scenario method will return false, model errors can be found in getErrors() method.
     *
     * Events: saving, saved, updating, updated will be fired.
     *
     * @param bool $validate Validate document fields and all children before saving, enabled by
     *                       default. Turning this option off will increase performance but will make
     *                       saving less secure. You can use it when model data was not modified
     *                       directly by user. By default value is null which will force document to
     *                       select behaviour from FORCE_VALIDATION constant.
     * @return bool
     * @throws ODMException
     */
    public function save($validate = null)
    {
        if (is_null($validate))
        {
            $validate = static::FORCE_VALIDATION;
        }

        if ($validate && !$this->isValid())
        {
            return false;
        }

        if (empty($this->collection) || $this->isEmbedded())
        {
            throw new ODMException(
                "Unable to save " . get_class($this) . ", no direct access to collection."
            );
        }

        if (!$this->isLoaded())
        {
            $this->event('saving');
            unset($this->fields['_id']);

            static::odmCollection($this->odm, $this->schema)->insert(
                $this->fields = $this->serializeData()
            );

            $this->event('saved');
        }
        elseif ($this->solidState || $this->hasUpdates())
        {
            $this->event('updating');

            static::odmCollection($this->odm, $this->schema)->update(
                ['_id' => $this->primaryKey()],
                $this->buildAtomics()
            );

            $this->event('updated');
        }

        $this->flushUpdates();

        return true;
    }

    /**
     * Delete document and all nested data from MongoCollection, document will be removed by primary
     * key (_id).
     *
     * Events: deleting, deleted will be raised.
     */
    public function delete()
    {
        if (!$this->collection)
        {
            throw new ODMException(
                "Unable to delete " . get_class($this) . ", no collection assigned."
            );
        }

        $this->event('deleting');
        $this->primaryKey() && static::odmCollection($this->odm, $this->schema)->remove([
            '_id' => $this->primaryKey()
        ]);

        $this->fields = $this->schema[ODM::D_DEFAULTS];
        $this->event('deleted');
    }

    /**
     * Create new model and set it's fields, all field values will be passed thought model filters
     * to ensure their type. Events: created
     *
     * @param array $fields Model fields to set, will be passed thought filters.
     * @param ODM   $odm    ODM component, will be received from Container if not provided.
     * @return static
     */
    public static function create($fields = [], ODM $odm = null)
    {
        /**
         * @var Document $class
         */
        $class = new static([], null, [], $odm);

        //Forcing validation (empty set of fields is not valid set of fields)
        $class->validationRequired = true;
        $class->setFields($fields)->event('created');

        return $class;
    }

    /**
     * Select multiple documents from associated collection. Attention, due ODM architecture, find
     * method can return any of Document types stored in collection, even if find called from specified
     * class. You have to solve it manually by overwrite this method in your class.
     *
     * @param mixed $query Fields and conditions to filter by.
     * @return Collection|static[]
     */
    public static function find(array $query = [])
    {
        return static::odmCollection()->query($query);
    }

    /**
     * Alias for find method. Select multiple documents from associated collection. Attention, due
     * ODM architecture, find method can return any of Document types stored in collection, even if
     * find called from specified class. You have to solve it manually by overwrite this method in
     * your class.
     *
     * @param mixed $query Fields and conditions to filter by.
     * @return Collection|static[]
     */
    public static function select(array $query = [])
    {
        return static::find($query);
    }

    /**
     * Select one document from collection.
     *
     * @param array $query  Fields and conditions to filter by.
     * @param array $sortBy Sorting.
     * @return static
     */
    public static function findOne(array $query = [], array $sortBy = [])
    {
        return static::find($query)->findOne();
    }

    /**
     * Select one document from collection by it's primary key, string key will be automatically
     * converted to MongoId object. Null will be returned if provided string is not valid mongo id.
     *
     * @param mixed $mongoID Valid MongoId, string value will be automatically converted to MongoId
     *                       object.
     * @return static
     * @throws ODMException
     */
    public static function findByPK($mongoID = null)
    {
        if (!$mongoID = ODM::mongoID($mongoID))
        {
            return null;
        }

        return static::findOne(['_id' => $mongoID]);
    }

    /**
     * Simplified way to dump information.
     *
     * @return Object
     */
    public function __debugInfo()
    {
        if (empty($this->collection))
        {
            return (object)[
                'fields'  => $this->getFields(),
                'atomics' => $this->buildAtomics(),
                'errors'  => $this->getErrors()
            ];
        }

        return (object)[
            'collection' => $this->database . '/' . $this->collection,
            'fields'     => $this->getFields(),
            'atomics'    => $this->buildAtomics(),
            'errors'     => $this->getErrors()
        ];
    }

    /**
     * Clear existed schema cache.
     */
    public static function clearSchemaCache()
    {
        self::$schemaCache = [];
    }
}