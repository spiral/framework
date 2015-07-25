<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ODM\Accessors;

use Spiral\Components\ODM\CompositableInterface;
use Spiral\Components\ODM\Document;
use Spiral\Components\ODM\ODM;
use Spiral\Components\ODM\ODMAccessor;
use Spiral\Components\ODM\ODMException;

/**
 * This class can be potentially should be merged with ORM collection and Models EntityIterator.
 */
class Compositor implements ODMAccessor, \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * Parent Document.
     *
     * @invisible
     * @var CompositableInterface
     */
    protected $parent = null;

    /**
     * ODM component.
     *
     * @var ODM
     */
    protected $odm = null;

    /**
     * Local composition schema, contain information about class definition.
     *
     * @var mixed
     */
    protected $classDefinition = null;

    /**
     * Compositor marked with solid state flag will be saved entirely without generating separate
     * atomic operations for each nested document, instead one big set operation will be called. Your
     * atomic() calls with be applied to document data but will not be forwarded to collection. All
     * compositors in solid state by default.
     *
     * @var bool
     */
    protected $solidState = true;

    /**
     * Set of documents to manage by Compositor.
     *
     * @var array|Document[]
     */
    protected $documents = [];

    /**
     * Set of document operations performed on compositor level, operations will include things like
     * pull, push and addToSet. Operations stored in this form to ensure that at moment of generating
     * atomic operation valid document form will be provided and no dup operation will be generated.
     *
     * @var array
     */
    protected $operations = [];

    /**
     * Indication that composition data were changed without using atomic operations, this flag will
     * be set to true if any document added, removed via array operations.
     *
     * @var bool
     */
    protected $changedDirectly = false;

    /**
     * Indication that validation is required, all sub documents will be forced to validate.
     *
     * @var bool
     */
    protected $validationRequired = false;

    /**
     * Error messages collected over nested documents.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * New instance of Compositor. Compositor used to perform various atomic operations and manipulations
     * with documents embedded to another document (as array).
     *
     * @param array|mixed           $data
     * @param CompositableInterface $parent
     * @param array|string          $classDefinition
     * @param ODM                   $odm ODM component.
     * @throws ODMException
     */
    public function __construct($data = null, $parent = null, $classDefinition = null, ODM $odm = null)
    {
        $this->parent = $parent;
        $this->odm = $odm;
        $this->documents = is_array($data) ? $data : [];
        if (!$this->classDefinition = $classDefinition)
        {
            throw new ODMException(
                "Compositor can not be created without defined class definition way."
            );
        }
    }

    /**
     * Change compositor solid state flag value. Compositor marked with solid state flag will be saved
     * entirely without generating separate atomic operations for each nested document, instead one
     * big set operation will be called. Your atomic() calls with be applied to document data but
     * will not be forwarded to collection.
     *
     * @param bool $solidState Solid state flag value.
     * @return $this|Document[]
     */
    public function solidState($solidState)
    {
        $this->solidState = $solidState;

        return $this;
    }

    /**
     * Define class name should be used to represent fields stored in composition.
     *
     * @param array $fields
     * @return string
     */
    protected function defineClass(array $fields)
    {
        return $this->odm->defineClass($fields, $this->classDefinition);
    }

    /**
     * Copy Compositable to embed into specified parent. Documents with already set parent will return
     * copy of themselves, in other scenario document will return itself.
     *
     * @param CompositableInterface $parent Parent ODMCompositable object should be copied or prepared
     *                                      for.
     * @return $this|self
     * @throws ODMException
     */
    public function embed($parent)
    {
        if (!$parent instanceof CompositableInterface)
        {
            throw new ODMException("Compositors can be embedded only to ODM objects.");
        }

        if (empty($this->parent))
        {
            $this->parent = $parent;

            return $this->solidState(true);
        }

        if ($parent === $this->parent)
        {
            return $this;
        }

        return new static($this->serializeData(), $parent, $this->classDefinition);
    }

    /**
     * Serialize object data for saving into database. This is common method for documents and
     * compositors.
     *
     * @return mixed
     */
    public function serializeData()
    {
        $result = [];
        foreach ($this->documents as $document)
        {
            $result[] = $document instanceof CompositableInterface
                ? $document->serializeData()
                : $document;
        }

        return $result;
    }

    /**
     * Get only public fields of nested documents.
     *
     * @return mixed
     */
    public function publicFields()
    {
        $result = [];
        foreach ($this as $document)
        {
            $result[] = $document->publicFields();
        }

        return $result;
    }

    /**
     * Get generated and manually set document/object atomic updates.
     *
     * @param string $container Name of field or index where document stored into.
     * @return array
     * @throws ODMException
     */
    public function buildAtomics($container = '')
    {
        if (!$this->hasUpdates())
        {
            return [];
        }

        if ($this->solidState)
        {
            return [Document::ATOMIC_SET => [
                $container => $this->serializeData()
            ]];
        }

        if ($this->changedDirectly)
        {
            throw new ODMException(
                "Composition were changed with low level array manipulations, "
                . "unable to generate atomic set (solid state off)."
            );
        }

        //Attention, you HAVE to disable solid stable to use atomic operations in sub objects
        $atomics = [];

        $handledDocuments = [];
        foreach ($this->operations as $operation => $items)
        {
            if ($operation != 'pull')
            {
                $handledDocuments = array_merge($handledDocuments, $items);

                foreach ($items as &$item)
                {
                    /**
                     * @var Document $item
                     */
                    $item = $item->serializeData();
                    unset($item);
                }
            }
            $atomics['$' . $operation][$container] = $items;
        }

        foreach ($this->documents as $offset => $document)
        {
            if ($document instanceof CompositableInterface)
            {
                if (in_array($document, $handledDocuments))
                {
                    //Handler on higher level
                    continue;
                }

                $atomics = array_merge(
                    $atomics,
                    $document->buildAtomics(
                        ($container ? $container . '.' : '') . $offset
                    )
                );
            }
        }

        return $atomics;
    }

    /**
     * Check if any nested object has any update.
     *
     * @return bool
     */
    public function hasUpdates()
    {
        if ($this->changedDirectly || !empty($this->operations))
        {
            return true;
        }

        foreach ($this->documents as $document)
        {
            if ($document instanceof CompositableInterface && $document->hasUpdates())
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Mark object as successfully updated and flush all existed atomic operations and updates.
     */
    public function flushUpdates()
    {
        $this->operations = [];
        $this->changedDirectly = false;

        foreach ($this->documents as $document)
        {
            if ($document instanceof CompositableInterface)
            {
                $document->flushUpdates();
            }
        }
    }

    /**
     * Accessor default value.
     *
     * @return mixed
     */
    public function defaultValue()
    {
        return [];
    }

    /**
     * Update accessor mocked data.
     *
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->changedDirectly = $this->solidState = true;

        if (!is_array($data))
        {
            //Ignoring
            return;
        }

        $this->documents = [];

        //Filling documents
        foreach ($data as $item)
        {
            is_array($item) && $this->create($item);
        }
    }

    /**
     * Create Document and add it to composition.
     *
     * @param array $fields
     * @return Document
     * @throws ODMException
     */
    public function create(array $fields = [])
    {
        if (!$this->solidState)
        {
            throw new ODMException(
                "Direct offset operation can not be performed for compositor in non solid state."
            );
        }

        $this->changedDirectly = true;
        $this->documents[] = $document = call_user_func([
            $this->odm->defineClass($fields, $this->classDefinition),
            'create'
        ], $fields, $this->odm)->embed($this);

        return $document;
    }

    /**
     * Clearing document composition.
     *
     * @return $this|Document[]
     */
    public function clear()
    {
        $this->solidState = $this->changedDirectly = true;
        $this->documents = [];

        return $this;
    }

    /**
     * Request validation.
     *
     * @return $this
     */
    public function requestValidation()
    {
        $this->validationRequired = true;

        return $this;
    }

    /**
     * Perform validation for all nested documents and return their errors in aggregated form.
     *
     * @return bool
     */
    protected function validate()
    {
        $this->errors = [];
        foreach ($this->documents as $offset => $document)
        {
            $document = $this->getDocument($offset);
            $this->validationRequired && $document->requestValidation();

            if (!$document->isValid())
            {
                $this->errors[$offset] = $document->getErrors();
            }
        }

        $this->validationRequired = false;
    }

    /**
     * Perform validation for all nested documents and return their errors in aggregated form and
     * result validation result.
     *
     * @return bool
     */
    public function isValid()
    {
        $this->validate();

        return !(bool)$this->errors;
    }

    /**
     * Errors raised in composited documents.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get document by array offset, instance will be automatically constructed if it's the first
     * call to the model.
     *
     * @param int $offset
     * @return Document
     */
    protected function getDocument($offset)
    {
        $document = $this->documents[$offset];

        if (!$document instanceof Document)
        {
            $class = $this->odm->defineClass($this->documents[$offset], $this->classDefinition);
            $this->documents[$offset] = $document = new $class($document, $this, [], $this->odm);
        }

        return $document;
    }

    /**
     * Find composited (nested document) by matched query. Query can be or array of fields, or
     * Document instance.
     *
     * @param array $query
     * @return array|Document[]
     */
    public function find($query = [])
    {
        if ($query instanceof Document)
        {
            //Not sure why you would do that...
            $query = $query->serializeData();
        }

        $result = [];
        foreach ($this->documents as $offset => $document)
        {
            //We have to pass document thought model construction to ensure default values
            $document = $this->getDocument($offset);
            $documentData = $document->serializeData();

            if (!$query || (array_intersect_assoc($documentData, $query) == $query))
            {
                $result[] = $document;
            }
        }

        return $result;
    }

    /**
     * Find first composited (nested document) by matched query. Query can be or array of fields, or
     * Document instance.
     *
     * @param array $query
     * @return null|Document
     */
    public function findOne($query = [])
    {
        if (!$documents = $this->find($query))
        {
            return null;
        }

        return $documents[0];
    }

    /**
     * Count of documents nested in compositor.
     *
     * @return int|void
     */
    public function count()
    {
        return count($this->documents);
    }

    /**
     * Retrieve an external iterator with all nested documents (in object form).
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable|Document[]
     */
    public function getIterator()
    {
        foreach ($this->documents as $offset => $document)
        {
            $this->getDocument($offset);
        }

        return new \ArrayIterator($this->documents);
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset An offset to check for.
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($offset)
    {
        return isset($this->documents[$offset]);
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset The offset to retrieve.
     * @return Document
     * @throws ODMException
     */
    public function offsetGet($offset)
    {
        if (!isset($this->documents[$offset]))
        {
            throw new ODMException("Undefined offset '{$offset}'.");
        }

        return $this->getDocument($offset);
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     * @return void
     * @throws ODMException
     */
    public function offsetSet($offset, $value)
    {
        if (!$this->solidState)
        {
            throw new ODMException(
                "Direct offset operation can not be performed for compositor in non solid state."
            );
        }

        $this->changedDirectly = true;
        if (is_null($offset))
        {
            $this->documents[] = $value;
        }
        else
        {
            $this->documents[$offset] = $value;
        }
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset The offset to unset.
     * @return void
     * @throws ODMException
     */
    public function offsetUnset($offset)
    {
        if (!$this->solidState)
        {
            throw new ODMException(
                "Direct offset operation can not be performed for compositor in non solid state."
            );
        }

        $this->changedDirectly = true;
        unset($this->documents[$offset]);
    }

    /**
     * Push new document to end of set.
     *
     * @param Document $document
     * @param bool     $ignoreState Set to true to reset compositor solid state.
     * @return $this|Document[]
     * @throws ODMException
     */
    public function push(Document $document, $ignoreState = true)
    {
        if ($ignoreState)
        {
            $this->solidState = false;
        }

        $this->documents[] = $document->embed($this);

        if ($this->solidState)
        {
            $this->changedDirectly = true;
        }
        else
        {
            if ($this->operations && !isset($this->operations['push']))
            {
                throw new ODMException(
                    "Unable to apply multiple atomic operation to composition."
                );
            }

            $this->operations['push'][] = $document;
        }

        return $this;
    }

    /**
     * Pulls document(s) from the set, query should represent document object matched fields.
     *
     * @param array|Document $query
     * @param bool           $ignoreState Set to true to reset compositor solid state.
     * @return $this|Document[]
     * @throws ODMException
     */
    public function pull($query, $ignoreState = true)
    {
        if ($ignoreState)
        {
            $this->solidState = false;
        }

        if ($query instanceof Document)
        {
            $query = $query->serializeData();
        }

        foreach ($this->documents as $offset => $document)
        {
            //We have to pass document thought model construction to ensure default values
            $document = $this->getDocument($offset)->serializeData();

            if (array_intersect_assoc($document, $query) == $query)
            {
                unset($this->documents[$offset]);
            }
        }

        if ($this->solidState)
        {
            $this->changedDirectly = true;
        }
        else
        {
            if ($this->operations && !isset($this->operations['pull']))
            {
                throw new ODMException("Unable to apply multiple atomic operation to composition.");
            }

            $this->operations['pull'][] = $query;
        }

        return $this;
    }

    /**
     * Add document to set, only one instance of document will be presented.
     *
     * @param Document $document
     * @param bool     $ignoreState Set to true to reset compositor solid state.
     * @return $this|Document[]
     * @throws ODMException
     */
    public function addToSet(Document $document, $ignoreState = true)
    {
        if ($ignoreState)
        {
            $this->solidState = false;
        }

        $found = false;
        foreach ($this->documents as $offset => $innerDocument)
        {
            if ($document->serializeData() == $this->getDocument($offset)->serializeData())
            {
                $found = true;
                break;
            }
        }

        if (!$found)
        {
            $this->documents[] = $document->embed($this);
        }

        if ($this->solidState)
        {
            $this->changedDirectly = true;
        }
        else
        {
            if ($this->operations && !isset($this->operations['addToSet']))
            {
                throw new ODMException("Unable to apply multiple atomic operation to composition.");
            }

            $this->operations['addToSet'][] = $document;
        }

        return $this;
    }

    /**
     * (PHP 5 >= 5.4.0)
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed
     */
    function jsonSerialize()
    {
        return $this->serializeData();
    }

    /**
     * Simplified way to dump information.
     *
     * @return Object
     */
    public function __debugInfo()
    {
        $this->validate();

        return (object)[
            'documents' => $this->serializeData(),
            'atomics'   => $this->buildAtomics('composition'),
            'errors'    => $this->getErrors()
        ];
    }
}