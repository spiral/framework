<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Models\Accessors;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Spiral\Components\Files\StreamContainerInterface;
use Spiral\Components\Storage\StorageContainer;
use Spiral\Components\Storage\StorageException;
use Spiral\Components\Storage\StorageManager;
use Spiral\Components\Storage\StorageObject;
use Spiral\Core\Component\LoggerTrait;
use Spiral\Support\Models\AccessorInterface;

/**
 * @method string getName()
 * @method string getAddress()
 * @method StorageContainer getContainer()
 * @method bool exists()
 * @method int|bool getSize()
 * @method string localFilename()
 * @method static rename($newname)
 * @method StorageObject copy($destination)
 * @method static replace($destination)
 */
abstract class StorageAccessor implements AccessorInterface, StreamContainerInterface
{
    /**
     * Some warnings.
     */
    use LoggerTrait;

    /**
     * Original address stored in db.
     *
     * @var string
     */
    protected $address = '';

    /**
     * Storage object.
     *
     * @var StorageObject
     */
    protected $storageObject = null;

    /**
     * Accessors can be used to mock different model values using "representative" class, like
     * DateTime for timestamps.
     *
     * @param mixed  $data    Data to mock.
     * @param object $parent
     * @param mixed  $options Implementation specific options.
     */
    public function __construct($data = null, $parent = null, $options = null)
    {
        if ($this->address = $data)
        {
            $this->storageObject = StorageManager::getInstance()->open($this->address);
        }
    }

    /**
     * Embed to another parent.
     *
     * @param object $parent
     * @return $this
     */
    public function embed($parent)
    {
        $accessor = clone $this;
        $accessor->address = '';

        self::logger()->warning(
            "Embedding existed StorageAccessor is not safe as "
            . "both accessors will point to same StorageObject."
        );

        return $accessor;
    }

    /**
     * Serialize accessor mocked value. This is legacy name and used like that to be compatible with
     * ORM and ODM engines.
     *
     * @return mixed
     */
    public function serializeData()
    {
        return !empty($this->storageObject) ? $this->storageObject->getAddress() : $this->address;
    }

    /**
     * Update accessor mocked data.
     *
     * @param mixed $data
     */
    public function setData($data)
    {
        if (is_string($data))
        {
            $this->storageObject = StorageManager::getInstance()->open($data);
        }

        if ($data instanceof StorageObject)
        {
            //Attaching object
            $this->storageObject = $data;
        }

        if (!empty($this->storageObject))
        {
            //Trying to update storage content
            $this->put($this->getContainer(), $this->getName(), $data);
        }
    }

    /**
     * Check if object has any update.
     *
     * @return bool
     */
    public function hasUpdates()
    {
        if (empty($this->address) && empty($this->storageObject))
        {
            return false;
        }

        if (empty($this->storageObject))
        {
            //Object detached
            return true;
        }

        //Checking if address changed
        return $this->address != $this->storageObject->getAddress();
    }

    /**
     * Mark object as successfully updated and flush all existed atomic operations and updates.
     */
    public function flushUpdates()
    {
        if (!empty($this->storageObject))
        {
            $this->address = $this->storageObject->getAddress();
        }
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *       which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return $this->serializeData();
    }

    /**
     * Convert accessor to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->serializeData();
    }

    /**
     * Check if storage object has associated data.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->address);
    }

    /**
     * Associated storage object with accessor (or update existed) using specified container, object
     * can be created as empty, using local filename, via Stream or using UploadedFile.
     *
     * @param string|StorageContainer                                    $container Container name, id
     *                                                                              or instance.
     * @param string                                                     $name      Object name should
     *                                                                              be used in container.
     * @param string|StreamInterface|UploadedFileInterface|StorageObject $origin    Local filename or
     *                                                                              Stream.
     * @return StorageObject|bool
     */
    public function put($container, $name, $origin = '')
    {
        $this->storageObject = StorageManager::getInstance()->put($container, $name, $origin);
    }

    /**
     * Check if accessor has associated storage object.
     *
     * @return bool
     */
    public function isAssociated()
    {
        return !empty($this->storageObject);
    }

    /**
     * Delete associated object and flush it's address to empty string.
     */
    public function delete()
    {
        if (!empty($this->storageObject))
        {
            $this->storageObject->delete();
        }

        $this->storageObject = null;
    }

    /**
     * Detach storage object without content removal.
     */
    public function detach()
    {
        $this->storageObject = null;
    }

    /**
     * Bypass call to storage object.
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        if (!$this->isAssociated())
        {
            throw new StorageException(
                "Unable to call decorated StorageObject, no instance assigned to accessor."
            );
        }

        $result = call_user_func_array([$this->storageObject, $method], $arguments);
        if ($result === $this->storageObject)
        {
            return $this;
        }

        return $result;
    }

    /**
     * Get associated stream.
     *
     * @return StreamInterface
     */
    public function getStream()
    {
        if (!$this->isAssociated())
        {
            throw new StorageException(
                "Unable to call decorated StorageObject, no instance assigned to accessor."
            );
        }

        return $this->storageObject->getStream();
    }
}