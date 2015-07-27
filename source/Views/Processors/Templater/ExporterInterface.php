<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater;

/**
 * ExporterInterface used to export outer node blocks into html source, for example it can convert
 * all additional include attributes into valid tag attributes, or create json/php array using them.
 */
interface ExporterInterface
{
    /**
     * Exported has to export (obviously) specified blocks into content. Every exporter should
     * defined it's own pattern to initiate export.
     *
     * @param string $content
     * @param array  $blocks
     */
    public function __construct($content, array $blocks);

    /**
     * Create content with mounted blocks (if any).
     *
     * @return string
     */
    public function mountBlocks();
}