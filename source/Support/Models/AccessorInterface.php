<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Models;

use Spiral\Support\Validation\ValueInterface;

interface AccessorInterface extends ValueInterface, \JsonSerializable
{
    /**
     * Accessors can be used to mock different model values using "representative" class, like
     * DateTime for timestamps.
     *
     * @param mixed  $data    Data to mock.
     * @param object $parent
     * @param mixed  $options Implementation specific options.
     */
    public function __construct($data = null, $parent = null, $options = null);

    /**
     * Embed to another parent.
     *
     * @param object $parent
     * @return $this
     */
    public function embed($parent);

    /**
     * Serialize accessor mocked value. This is legacy name and used like that to be compatible with
     * ORM and ODM engines.
     *
     * @return mixed
     */
    public function serializeData();

    /**
     * Update accessor mocked data.
     *
     * @param mixed $data
     */
    public function setData($data);
}