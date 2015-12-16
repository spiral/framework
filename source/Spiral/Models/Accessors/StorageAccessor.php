<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Models\Accessors;

use Psr\Http\Message\StreamInterface;
use Spiral\Core\Component;
use Spiral\Database\Entities\Driver;
use Spiral\Debug\Traits\LoggerTrait;
use Spiral\Files\Streams\StreamableInterface;
use Spiral\Models\DataEntity;
use Spiral\Models\EntityInterface;
use Spiral\Models\Exceptions\StorageAccessorException;
use Spiral\ODM\Document;
use Spiral\ODM\DocumentAccessorInterface;
use Spiral\ORM\RecordAccessorInterface;
use Spiral\Storage\BucketInterface;
use Spiral\Storage\Exceptions\BucketException;
use Spiral\Storage\Exceptions\ServerException;
use Spiral\Storage\Exceptions\StorageException;
use Spiral\Storage\ObjectInterface;
use Spiral\Storage\StorageInterface;

/**
 * Provides simplified access to field pointing to storage object address. Global container instance
 * is required in fallback mode!
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
class StorageAccessor extends Component implements
    RecordAccessorInterface,
    DocumentAccessorInterface,
    StreamableInterface
{
    /**
     * Logging warnings.
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
     * @var ObjectInterface
     */
    protected $object = null;

    /**
     * @invisible
     * @var DataEntity
     */
    private $parent = null;

    /**
     * @invisible
     * @var StorageInterface
     */
    private $storage = null;

    /**
     * {@inheritdoc}
     */
    public function __construct($data = null, EntityInterface $parent = null)
    {
        if ($this->address = $data) {
            $this->object = $this->storage()->open($this->address);
        }
    }

    /**
     * Set storage manager component, if no component set - global container will be used.
     *
     * @param StorageInterface $storage
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function embed(EntityInterface $parent)
    {
        $accessor = clone $this;
        $accessor->address = '';

        $this->logger()->warning(
            "StorageAccessor embedding is not safe, two accessors can point to same address."
        );

        return $accessor;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($data)
    {
        if (is_string($data)) {
            $this->object = $this->storage()->open($data);

            return;
        }

        if ($data instanceof ObjectInterface) {
            //Attaching object
            $this->object = $data;

            return;
        }

        if (empty($this->object)) {
            throw new StorageAccessorException(
                "Unable to put data to StorageObject without specifying it's name. Use put method."
            );
        }

        //Replacing existed content
        $this->put($this->getBucket(), $this->getName(), $data);
    }

    /**
     * {@inheritdoc}
     */
    public function serializeData()
    {
        return !empty($this->object) ? $this->object->getAddress() : $this->address;
    }

    /**
     * {@inheritdoc}
     */
    public function hasUpdates()
    {
        if (empty($this->address) && empty($this->object)) {
            return false;
        }

        if (empty($this->object)) {
            //Object detached
            return true;
        }

        //Checking if address changed
        return $this->address != $this->object->getAddress();
    }

    /**
     * {@inheritdoc}
     */
    public function flushUpdates()
    {
        if (!empty($this->object)) {
            $this->address = $this->object->getAddress();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function compileUpdates($field = '')
    {
        return $this->serializeData();
    }

    /**
     * {@inheritdoc}
     */
    public function buildAtomics($container = '')
    {
        if (!$this->hasUpdates()) {
            return [];
        }

        if (!empty($this->address) && empty($this->storageObject)) {
            //Object detached
            return [Document::ATOMIC_SET => [$container => '']];
        }

        return [Document::ATOMIC_SET => [$container => $this->storageObject->getAddress()]];
    }

    /**
     * {@inheritdoc}
     */
    public function defaultValue(Driver $driver = null)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->serializeData();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->serializeData();
    }

    /**
     * Bypass call to mocked storage object.
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed
     * @throws StorageAccessorException
     */
    public function __call($method, array $arguments)
    {
        if (!$this->isAssociated()) {
            throw new StorageAccessorException(
                "Unable to call decorated StorageObject, no instance has been assigned to accessor."
            );
        }

        $result = call_user_func_array([$this->object, $method], $arguments);
        if ($result === $this->object) {
            return $this;
        }

        return $result;
    }

    /**
     * /**
     * Put object data into specified bucket under provided name. Should support filenames, PSR7
     * streams and streamable objects. Must create empty object if source empty.
     *
     * @param string|BucketInterface                    $bucket
     * @param string                                    $name
     * @param mixed|StreamInterface|StreamableInterface $source
     * @return $this
     * @throws StorageException
     * @throws BucketException
     * @throws ServerException
     */
    public function put($bucket, $name, $source = '')
    {
        $this->object = $this->storage()->put($bucket, $name, $source);

        return $this;
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
     * @throws StorageAccessorException
     */
    public function getStream()
    {
        if (!$this->isAssociated()) {
            throw new StorageAccessorException(
                "Unable to call decorated StorageObject, no instance assigned to accessor."
            );
        }

        return $this->object->getStream();
    }

    /**
     * Storage component. Global container is required!
     *
     * @return StorageInterface
     */
    private function storage()
    {
        if (!empty($this->storage)) {
            return $this->storage;
        }

        if (!empty($this->parent) && $this->parent instanceof Component) {
            return $this->storage = $this->parent->container()->get(StorageInterface::class);
        }

        //Only when global container is set and no parent container specified
        return $this->storage = self::staticContainer()->get(StorageInterface::class);
    }
}