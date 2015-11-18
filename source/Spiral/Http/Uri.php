<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http;

/**
 * {@inheritdoc}
 */
class Uri extends \Zend\Diactoros\Uri implements \JsonSerializable
{
    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->__toString();
    }

    /**
     * @return object
     */
    public function __debugInfo()
    {
        return (object)[
            'uri'    => (string)$this,
            'chunks' => [
                'scheme'    => $this->getScheme(),
                'authority' => $this->getAuthority(),
                'host'      => $this->getHost(),
                'port'      => $this->getPort(),
                'path'      => $this->getPath(),
                'query'     => $this->getQuery(),
                'fragment'  => $this->getFragment()
            ]
        ];
    }
}