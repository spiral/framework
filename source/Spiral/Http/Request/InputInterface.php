<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Request;

use Spiral\Http\Exceptions\InputException;

/**
 * Provides values for RequestFilter.
 *
 * @todo Move on another level.
 */
interface InputInterface
{
    /**
     * Get input value based on it's source and name.
     *
     * @param string $source
     * @param string $name
     *
     * @return mixed
     *
     * @throws InputException
     */
    public function getValue(string $source, string $name = null);
}