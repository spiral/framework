<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Validation;

use Spiral\Validation\Exception\ParserException;

interface ParserInterface
{
    /**
     * Split rule definition into multiple chunks. Chunks will be fed into
     * `fetchArgs`, `fetchMessage` methods.
     *
     * Method must return unique rule id as key.
     *
     * @param mixed $rules
     *
     * @throws ParserException
     */
    public function split($rules): \Generator;

    /**
     * Return function name, class method pair or short "checker:method" definition
     * associated with the rule.
     *
     * @param mixed $chunk
     * @return string|array|callable
     *
     * @throws ParserException
     */
    public function parseCheck($chunk);

    /**
     * Fetch validation rule arguments from rule definition.
     *
     * @param mixed $chunk
     *
     * @throws ParserException
     */
    public function parseArgs($chunk): array;

    /**
     * Fetch error message from rule definition or use default message. Method will check "message"
     * and "error" properties of definition.
     *
     * @param mixed $chunk
     * @return string
     *
     * @throws ParserException
     */
    public function parseMessage($chunk): ?string;

    /**
     * Parse validation conditions in a form of array [conditionClass => options].
     *
     * @param mixed $chunk
     *
     * @throws ParserException
     */
    public function parseConditions($chunk): array;
}
