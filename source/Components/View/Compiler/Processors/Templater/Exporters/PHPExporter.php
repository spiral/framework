<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater\Exporters;

use Spiral\Components\View\Compiler\Processors\Templater\AbstractExporter;

/**
 * Will export specified (or all) import attributes into valid PHP array.
 */
class PHPExporter extends AbstractExporter
{
    /**
     * Create content with mounted blocks (if any).
     *
     * @return string
     */
    public function mountBlocks()
    {
        //TODO: NOT IMPLEMENTED YET

        return $this->content;
    }
}