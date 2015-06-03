<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Reactor\Creators;

use Spiral\Components\Files\FileManager;
use Spiral\Core\Component;
use Spiral\Helpers\StringHelper;
use Spiral\Support\Generators\Reactor\ClassElement;
use Spiral\Support\Generators\Reactor\FileElement;

abstract class ClassCreator extends Component
{
    /**
     * ClassElement instance.
     *
     * @var ClassElement
     */
    public $class = '';

    /**
     * FileElement instance. Code will be written to this file.
     *
     * @var FileElement
     */
    public $file = null;

    /**
     * ClassCreator used to render different declarations such as Controllers, Models and etc. All
     * rendering performed using Reactor classes.
     *
     * @param string $name Target class name.
     */
    public function __construct($name)
    {
        $this->class = $this->generateName($name);
        $this->file = FileElement::make();
        $this->file->addClass($this->class);
    }

    /**
     * Resolve class name based on user input.
     *
     * @param string $name
     * @return ClassElement
     */
    protected function generateName($name)
    {
        $name = StringHelper::urlSlug(ucwords(str_replace('_', ' ', $name)), '');

        return ClassElement::make(compact('name'));
    }

    /**
     * Render the PHP file's code and deliver it to a given filename.
     *
     * @param string $filename        Filename to render code into.
     * @param int    $mode            Use File::RUNTIME for 666 and File::READONLY for application
     *                                files.
     * @param bool   $ensureDirectory If true, helper will ensure that the destination directory
     *                                exists and has the correct permissions.
     * @return bool
     */
    public function render($filename, $mode = FileManager::READONLY, $ensureDirectory = true)
    {
        return $this->file->renderFile($filename, $mode, $ensureDirectory);
    }
}