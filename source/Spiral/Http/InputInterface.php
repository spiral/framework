<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http;

use Spiral\Http\Exceptions\InputException;

/**
 * Provides values for RequestFilter.
 */
interface InputInterface
{
    /**
     * Get input value.
     *
     * @param string $source
     * @param string $name
     * @return mixed
     * @throws InputException
     */
    public function getValue($source, $name = null);
}