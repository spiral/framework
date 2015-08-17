<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */

namespace Spiral\Documenters;

use Spiral\Core\Component;
use Spiral\Files\FilesInterface;
use Spiral\Reactor\FileElement;

/**
 * Provides basic needs for any spiral documenter implementation such as easier access to reactor
 * classes, schema cloning and generation of support classes.
 */
class VirtualDocumenter extends Component
{
    /**
     * File to render virtual documentation into.
     *
     * @var FileElement
     */
    protected $file = null;

    /**
     * Stores configuration.
     *
     * @invisible
     * @var Documenter
     */
    protected $documenter = null;

    /**
     * @param Documenter     $documenter
     * @param FilesInterface $files
     */
    public function __construct(Documenter $documenter, FilesInterface $files)
    {
        $this->documenter = $documenter;

        //Documenter must declare namespace for virtual classes
        $this->file = new FileElement($files, $documenter->config()['namespace']);
    }

    /**
     * Render virtual documentation into file.
     *
     * @param string $filename
     * @return bool
     */
    public function render($filename)
    {
        return $this->file->renderTo($filename, FilesInterface::RUNTIME, true);
    }
}