<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor;

use Spiral\Reactor\Traits\DocCommentTrait;
use Spiral\Reactor\Traits\UsesTrait;

/**
 * Provides ability to render file content.
 */
class FileDeclaration implements RenderableInterface
{
    use UsesTrait, DocCommentTrait;

    /**
     * File namespace.
     *
     * @var string
     */
    private $namespace = '';

    /**
     * @var RenderableInterface[]
     */
    private $elements = [];

    /**
     * @param string $namespace
     * @param string $comment
     */
    public function __construct($namespace = '', $comment = '')
    {
        $this->namespace = $namespace;
        $this->docComment = new DocComment();

        if (!empty($comment)) {
            if (is_array($comment)) {
                $this->docComment->setLines($comment);
            } elseif (is_string($comment)) {
                $this->docComment->setString($comment);
            }
        }
    }

    /**
     * Add element into file.
     *
     * @param RenderableInterface $element
     * @return $this
     */
    public function add(RenderableInterface $element)
    {
        $this->elements[] = $element;

        return $this;
    }

    /**
     * @param ClassDeclaration $class
     * @return $this
     */
    public function addClass(ClassDeclaration $class)
    {
        $this->elements[] = $class;

        return $this;
    }

    /**
     * @param NamespaceDeclaration $namespace
     * @return $this
     */
    public function addNamespace(NamespaceDeclaration $namespace)
    {
        $this->elements[] = $namespace;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render($indentLevel = 0)
    {
        $result = "<?php\n";

        if (!$this->docComment->isEmpty()) {
            $result .= $this->docComment->render($indentLevel) . "\n";
        }

        if (!empty($this->namespace)) {
            $result .= "namespace {$this->namespace};\n";
        }

        if ($this->hasUses()) {
            $result .= $this->renderUses($indentLevel) . "\n";
        }

        foreach ($this->elements as $element) {
            $result .= $element->render($indentLevel);
        }

        return $result;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render(0);
    }
}