<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Request;

class HeaderBag extends ParameterBag
{
    /**
     * Parameter bag used to perform read only operations with request attributes.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        foreach ($parameters as $header => $values)
        {
            $this->data[$header] = join(',', $values);
        }
    }
}