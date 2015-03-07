<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Generators\Reactor;

use Spiral\Components\Files\FileManager;

class FileElement extends NamespaceElement
{
    /**
     * PHP file open tag.
     */
    const PHP_OPEN = '<?php';

    /**
     * All elements nested to the PHP file, that can include classes and namespaces.
     *
     * @var BaseElement[]
     */
    protected $elements = array();

    /**
     * FileManager component.
     *
     * @var FileManager
     */
    protected $file = null;

    /**
     * New instance of RPHPFile, class used to write Reactor declarations to specified filename.
     *
     * @param mixed       $namespace
     * @param FileManager $file
     */
    public function __construct($namespace = null, FileManager $file)
    {
        parent::__construct($namespace);
        $this->file = $file;
    }

    /**
     * Adding a new reactor element to a file.
     *
     * @param BaseElement $element
     * @return static
     */
    public function addElement(BaseElement $element)
    {
        $this->elements[] = $element;

        return $this;
    }

    /**
     * Add a new class declaration to a file.
     *
     * @param ClassElement $class
     * @return static
     */
    public function addClass(ClassElement $class)
    {
        $this->elements[] = $class;

        return $this;
    }

    /**
     * Render the PHP file's code and deliver it to a given filename.
     *
     * @param string $filename        Filename to render code into.
     * @param int    $mode            Use File::RUNTIME for 666 and File::READONLY for application files.
     * @param bool   $ensureDirectory If true, helper will ensure that the destination directory exists and has the correct
     *                                permissions.
     * @return bool
     */
    public function renderFile($filename, $mode = FileManager::RUNTIME, $ensureDirectory = false)
    {
        return $this->file->write($filename, $this->createDeclaration(), $mode, $ensureDirectory);
    }

    /**
     * Render element declaration. This method should be declared in RElement child classes and perform an operation for
     * rendering the specific type of content. RPHPFile will render all nested classes and namespaces into valid (in terms of
     * syntax) php code.
     *
     * @param int $indentLevel Tabulation level.
     * @return string
     */
    public function createDeclaration($indentLevel = 0)
    {
        $result = array(self::PHP_OPEN, trim($this->renderComment($indentLevel)));

        if ($this->name)
        {
            $result[] = 'namespace ' . $this->name . ';';
        }

        //Uses
        foreach ($this->uses as $class)
        {
            $result[] = self::applyIndent('use ' . $class . ';', $indentLevel);
        }

        //Classes
        foreach ($this->elements as $element)
        {
            $result[] = $element->createDeclaration($indentLevel);
        }

        return self::join($result, $indentLevel);
    }
}