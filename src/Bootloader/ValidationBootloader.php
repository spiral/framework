<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader;

use Spiral\Core\Bootloader\Bootloader;
use Spiral\Validation\ParserInterface;
use Spiral\Validation\RuleParser;
use Spiral\Validation\RulesInterface;
use Spiral\Validation\ValidationInterface;
use Spiral\Validation\ValidationProvider;

class ValidationBootloader extends Bootloader
{
    const SINGLETONS = [
        ValidationInterface::class => ValidationProvider::class,
        RulesInterface::class      => ValidationProvider::class,
        ParserInterface::class     => RuleParser::class
    ];
}