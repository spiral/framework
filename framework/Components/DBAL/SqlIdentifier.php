<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL;

class SqlIdentifier implements SqlIdentifierInterface
{
    /**
     * Identifier content.
     *
     * @var string
     */
    protected $identifier = '';

    /**
     * SQLIdentifiers used by join and nested queries to specify that where parameter should not
     * be treated as simple value but as identifier.
     *
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Get identifier content.
     *
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}