<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor;

use Spiral\Files\FilesInterface;

/**
 * Represent one PHP file and can be written directly to harddrive.
 *
 * @deprecated To be replaced with Zend Code.
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
     * @return ClassElement[]|NamespaceElement[]
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Render file declaration into file.
     *
     * @param string          $filename
     * @param int             $mode
     * @param bool            $ensureDirectory
     * @param ArraySerializer $exporter Class used to render array values for default properties
     *                                  and etc.
     * @return bool
     */
    public function render(
        $filename,
        $mode = FilesInterface::RUNTIME,
        $ensureDirectory = false,
        ArraySerializer $exporter = null
    ) {
        return $this->files->write(
            $filename,
            join("\n", $this->lines(0, $exporter)),
            $mode,
            $ensureDirectory
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param ArraySerializer $serializer Class used to render array values for default properties
     *                                    and etc.
     */
    public function lines($indent = 0, ArraySerializer $serializer = null)
    {
        $result = array_merge([self::PHP_OPEN], $this->commentLines());

        if (!empty($this->getName())) {
            $result[] = "namespace {$this->getName()};";
        }

        //Uses
        foreach ($this->uses as $class) {
            $result[] = "use {$class};";
        }

        $result[] = "";

        //Classes
        foreach ($this->elements as $element) {
            //Same level
            $result = array_merge($result, $element->lines(0, $serializer));
            $result[] = "";
        }

        if ($result[count($result) - 1] == "") {
            //We don't need blank lines at the end
            unset($result[count($result) - 1]);
        }

        return $this->indentLines($result, $indent);
    }
}