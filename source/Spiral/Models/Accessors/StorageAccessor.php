<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Models\Accessors;

use Psr\Http\Message\StreamInterface;
use Spiral\Core\Component;
use Spiral\Database\Driver;
use Spiral\Debug\Traits\LoggerTrait;
use Spiral\Files\Streams\StreamableInterface;
use Spiral\ODM\Document;
use Spiral\ODM\ODM;
use Spiral\ODM\ODMAccessor;
use Spiral\ORM\ORMAccessor;
use Spiral\Storage\BucketInterface;
use Spiral\Storage\ObjectInterface;
use Spiral\Storage\StorageException;
use Spiral\Storage\StorageInterface;

/**
 * Provides simplified access to field pointing to storage object address. Global container instance
 * is required!
 *
 * @method string getName()
 * @method string getAddress()
 * @method BucketInterface getBucket()
 * @method bool exists()
 * @method int|bool getSize()
 * @method string localFilename()
 * @method static rename($newname)
 * @method static copy($destination)
 * @method static replace($destination)
 */
class StorageAccessor extends Component implements ORMAccessor, ODMAccessor, StreamableInterface
{
    /**
     * Logging warnings.
     */
    use LoggerTrait;

    /**
     * Storage Component.
     *
     * @var StorageInterface
     */
    private $storage = null;

    /**
     * Original address stored in db.
     *
     * @var string
     */
    protected $address = '';

    /**
     * Storage object.
     *
     * @var ObjectInterface
     */
    protected $object = null;

    /**
     * Accessors can be used to mock different model values using "representative" class, like
     * DateTime for timestamps.
     *
     * @param mixed  $data    Data to mock.
     * @param object $parent
     * @param mixed  $options Implementation specific options.
     * @param ODM    $odm     Required to be used as ODM accessor.
     */
    public function __construct($data = null, $parent = null, $options = null, ODM $odm = null)
    {
        if ($this->address = $data)
        {
            $this->object = $this->storage()->open($this->address);
        }
    }

    /**
     * Storage component.
     *
     * @return StorageInterface
     */
    private function storage()
    {
        if (!empty($this->storage))
        {
            return $this->storage;
        }

        return $this->storage = self::getContainer()->get(StorageInterface::class);
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

        $this->logger()->warning(
            "StorageAccessor embedding is not safe, two accessors can point to same address."
        );

        return $accessor;
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
            $this->object = $this->storage()->open($data);

            return;
        }

        if ($data instanceof ObjectInterface)
        {
            //Attaching object
            $this->object = $data;

            return;
        }

        //Trying to update storage content
        !empty($this->object) && $this->put($this->getContainer(), $this->getName(), $data);
    }

    /**
     * Serialize accessor mocked value. This is legacy name and used like that to be compatible with
     * ORM and ODM engines.
     *
     * @return mixed
     */
    public function serializeData()
    {
        return !empty($this->object) ? $this->object->getAddress() : $this->address;
    }

    /**
     * Check if object has any update.
     *
     * @return bool
     */
    public function hasUpdates()
    {
        if (empty($this->address) && empty($this->object))
        {
            return false;
        }

        if (empty($this->object))
        {
            //Object detached
            return true;
        }

        //Checking if address changed
        return $this->address != $this->object->getAddress();
    }

    /**
     * Mark object as successfully updated and flush all existed atomic operations and updates.
     */
    public function flushUpdates()
    {
        if (!empty($this->object))
        {
            $this->address = $this->object->getAddress();
        }
    }

    /**
     * Get new field value to be send to database.
     *
     * @param string $field Name of field where model/accessor stored into.
     * @return mixed
     */
    public function compileUpdates($field = '')
    {
        return $this->serializeData();
    }

    /**
     * Get generated and manually set document/object atomic updates.
     *
     * @param string $container Name of field or index where document stored into.
     * @return array
     */
    public function buildAtomics($container = '')
    {
        if (!$this->hasUpdates())
        {
            return [];
        }

        if (!empty($this->address) && empty($this->storageObject))
        {
            //Object detached
            return [Document::ATOMIC_SET => [$container => '']];
        }

        return [Document::ATOMIC_SET => [$container => $this->storageObject->getAddress()]];
    }

    /**
     * Accessor default value specific to driver.
     *
     * @param Driver $driver
     * @return mixed
     */
    public function defaultValue(Driver $driver = null)
    {
        return '';
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
                "Unable to call decorated StorageObject, no instance has been assigned to accessor."
            );
        }

        if (($result = call_user_func_array([$this->object, $method], $arguments)) === $this->object)
        {
            return $this;
        }

        return $result;
    }

    /**
     * Associated storage object (or update existed) with specified bucket, object can be created
     * as empty, using local filename, via Stream or using UploadedFile.
     *
     * While object creation original filename, name (no extension) or extension can be embedded to
     * new object name using string interpolation ({name}.{ext}}
     *
     * Example (using Facades):
     * Storage::create('cloud', $id . '-{name}.{ext}', $filename);
     * Storage::create('cloud', $id . '-upload-{filename}', $filename);
     *
     * @param string|BucketInterface                     $bucket    Bucket name, id or instance.
     * @param string                                     $name      Object name should be used in
     *                                                              bucket.
     * @param string|StreamInterface|StreamableInterface $origin    Local filename or Stream.
     * @return ObjectInterface|bool
     */
    public function put($bucket, $name, $origin = '')
    {
        $this->object = $this->storage()->put($bucket, $name, $origin);
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
     * Check if accessor has associated storage object.
     *
     * @return bool
     */
    public function isAssociated()
    {
        return !empty($this->object);
    }

    /**
     * Delete associated object and flush it's address to empty string.
     */
    public function delete()
    {
        !empty($this->object) && $this->object->delete();
        $this->object = null;
    }

    /**
     * Detach storage object without content removal.
     */
    public function detach()
    {
        $this->object = null;
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

        return $this->object->getStream();
    }
}