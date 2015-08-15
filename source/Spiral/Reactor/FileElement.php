<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor;

use Spiral\Files\FilesInterface;

/**
 * Represent one PHP file and can be written directly to harddrive.
 */
class FileElement extends NamespaceElement
{
    /**
     * PHP file open tag.
     */
    const PHP_OPEN = '<?php';

    /**
     * @var ClassElement[]|NamespaceElement[]
     */
    private $elements = [];

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @param FilesInterface $files
     * @param mixed          $namespace
     */
    public function __construct(FilesInterface $files, $namespace = '')
    {
        parent::__construct($namespace);
        $this->files = $files;
    }

    /**
     * Set file namespace.
     *
     * @param string $namespace
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $this->setName($namespace);

        return $this;
    }

    /**
     * Active file namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->getName();
    }

    /**
     * @param ClassElement $class
     * @return $this
     */
    public function addClass(ClassElement $class)
    {
        $this->elements[] = $class;

        return $this;
    }

    /**
     * @param NamespaceElement $namespace
     * @return $this
     */
    public function addNamespace(NamespaceElement $namespace)
    {
        $this->elements[] = $namespace;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param ArraySerializer $serializer Class used to render array values for default properties and etc.
     */
    public function render($indentLevel = 0, ArraySerializer $serializer = null)
    {
        $result = [self::PHP_OPEN, trim($this->renderComment($indentLevel))];

        if (!empty($this->getName())) {
            $result[] = 'namespace ' . $this->getName() . ';';
        }

        //Uses
        foreach ($this->uses as $class) {
            $result[] = $this->indent('use ' . $class . ';', $indentLevel);
        }

        //Classes
        foreach ($this->elements as $element) {
            $result[] = $element->render($indentLevel, $serializer);
        }

        return $this->join($result, $indentLevel);
    }

    /**
     * Render file declaration into file.
     *
     * @param string          $filename
     * @param int             $mode
     * @param bool            $ensureLocation
     * @param ArraySerializer $exporter Class used to render array values for default properties and etc.
     * @return bool
     */
    public function renderTo(
        $filename,
        $mode = FilesInterface::RUNTIME,
        $ensureLocation = false,
        ArraySerializer $exporter = null
    ) {
        return $this->files->write($filename, $this->render(0, $exporter), $mode, $ensureLocation);
    }
}