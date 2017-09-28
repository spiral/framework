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

    /**
     * Create version of input isolated by a given prefix.
     *
     * In a given examples listed method must produce same result:
     *
     * $input->getValue('data', 'array.value')
     * $input->withPrefix('array')->getValue('data', 'value')
     *
     * @param string $prefix
     *
     * @param bool $add When set to false current prefix path will be overwritten.
     *
     * @return InputInterface
     */
    public function withPrefix(string $prefix, bool $add = true): InputInterface;
}