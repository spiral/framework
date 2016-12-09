<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http;

/**
 * Provides ability to be json serialized.
 */
class Uri extends \Zend\Diactoros\Uri implements \JsonSerializable
{
    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return (string)$this;
    }
}