<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Views;

use Spiral\Views\Exceptions\CompilerException;

/**
 * View processors used to prepare/compile view source with specific set of operations. Generally
 * used inside default spiral compiler.
 */
interface ProcessorInterface
{
    /**
     * @param ViewManager $views
     * @param Compiler    $compiler
     * @param array       $options
     */
    public function __construct(ViewManager $views, Compiler $compiler, array $options);

    /**
     * Compile view source.
     *
     * @param string $source View source (code).
     * @return string
     * @throws CompilerException
     * @throws \ErrorException
     */
    public function process($source);
}