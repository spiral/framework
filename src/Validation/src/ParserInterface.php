<?php

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
     * @throws ParserException
     */
    public function split(mixed $rules): \Generator;

    /**
     * Return function name, class method pair or short "checker:method" definition
     * associated with the rule.
     *
     * @throws ParserException
     */
    public function parseCheck(mixed $chunk): array|callable|string;

    /**
     * Fetch validation rule arguments from rule definition.
     *
     * @throws ParserException
     */
    public function parseArgs(mixed $chunk): array;

    /**
     * Fetch error message from rule definition or use default message. Method will check "message"
     * and "error" properties of definition.
     *
     * @throws ParserException
     */
    public function parseMessage(mixed $chunk): ?string;

    /**
     * Parse validation conditions in a form of array [conditionClass => options].
     *
     * @throws ParserException
     */
    public function parseConditions(mixed $chunk): array;
}
