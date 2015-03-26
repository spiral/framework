<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ODM;

use Spiral\Support\Models\AccessorInterface;

interface CompositableInterface extends AccessorInterface
{
    /**
     * New Compositable instance. No type specified to keep it compatible with AccessorInterface.
     *
     * @param array|mixed           $data
     * @param CompositableInterface $parent
     * @param mixed                 $options Implementation specific options.
     */
    public function __construct($data = null, $parent = null, $options = null);

    /**
     * Copy Compositable to embed into specified parent. Documents with already set parent will return
     * copy of themselves, in other scenario document will return itself. No type specified to keep
     * it compatible with AccessorInterface.
     *
     * @param CompositableInterface $parent Parent ODMCompositable object should be copied or prepared
     *                                      for.
     * @return CompositableInterface
     */
    public function embed($parent);

    /**
     * Get generated and manually set document/object atomic updates.
     *
     * @param string $container Name of field or index where document stored into.
     * @return array
     */
    public function buildAtomics($container = '');

    /**
     * Check if object has any update.
     *
     * @return bool
     */
    public function hasUpdates();

    /**
     * Mark object as successfully updated and flush all existed atomic operations and updates.
     */
    public function flushUpdates();
}