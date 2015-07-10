<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater;

use Spiral\Components\View\Compiler\Processors\TemplateProcessor;
use Spiral\Components\View\ViewManager;

/**
 * ImportInterface used by templater to defined what tags should be treated as includes and how to
 * resolve their view or namespace.
 */
interface ImporterInterface
{
    /**
     * New instance of importer.
     *
     * @param ViewManager       $viewManager
     * @param TemplateProcessor $templater
     * @param array             $options
     */
    public function __construct(ViewManager $viewManager, TemplateProcessor $templater, array $options);
}