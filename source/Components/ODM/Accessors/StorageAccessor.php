<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ODM\Accessors;

use Psr\Http\Message\StreamInterface;
use Spiral\Components\ODM\CompositableInterface;
use Spiral\Components\ODM\ODM;
use Spiral\Components\ODM\ODMAccessor;
use Spiral\Components\Storage\StorageContainer;
use Spiral\Components\Storage\StorageObject;
use Spiral\Components\Storage\Accessors\StorageAccessor as AbstractStorageAccessor;

/**
 * @method string getName()
 * @method string getAddress()
 * @method StorageContainer getContainer()
 * @method bool exists()
 * @method int|bool getSize()
 * @method string localFilename()
 * @method StreamInterface getStream()
 * @method static rename($newname)
 * @method StorageObject copy($destination)
 * @method static replace($destination)
 */
class StorageAccessor extends AbstractStorageAccessor implements ODMAccessor
{
    /**
     * New Compositable instance. No type specified to keep it compatible with AccessorInterface.
     *
     * @param array|mixed           $data
     * @param CompositableInterface $parent
     * @param mixed                 $options Implementation specific options.
     * @param ODM                   $odm     ODM component.
     */
    public function __construct($data = null, $parent = null, $options = null, ODM $odm = null)
    {
        parent::__construct($data, $parent, $options);
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
            return ['$set' => [$container => '']];
        }

        return ['$set' => [
            $container => $this->storageObject->getAddress()
        ]];
    }

    /**
     * Accessor default value.
     *
     * @return mixed
     */
    public function defaultValue()
    {
        return '';
    }
}