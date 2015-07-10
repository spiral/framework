<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater;

abstract class AbstractExporter implements ExporterInterface
{
    /**
     * Content where exported blocks should be injected into.
     *
     * @var string
     */
    protected $content = '';

    /**
     * Blocks to be injected.
     *
     * @var string
     */
    protected $blocks = '';

    /**
     * Exported has to export (obviously) specified blocks into content. Every exporter should
     * defined it's own pattern to initiate export.
     *
     * @param string $content
     * @param array  $blocks
     */
    public function __construct($content, array $blocks)
    {
        $this->content = $content;
        $this->blocks = $blocks;
    }
}