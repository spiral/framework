<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader;

use Spiral\Core\Bootloader\Bootloader;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\ClassLocator;
use Spiral\Tokenizer\InvocationLocator;
use Spiral\Tokenizer\InvocationsInterface;
use Spiral\Tokenizer\Tokenizer;
use Spiral\Tokenizer\TokenizerInterface;

class TokenizerBootloader extends Bootloader
{
    const BINDINGS = [
        TokenizerInterface::class   => Tokenizer::class,
        ClassesInterface::class     => ClassLocator::class,
        InvocationsInterface::class => InvocationLocator::class
    ];
}