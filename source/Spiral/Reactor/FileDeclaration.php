<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor;

use Spiral\Reactor\Prototypes\Declaration;
use Spiral\Reactor\Traits\CommentTrait;
use Spiral\Reactor\Traits\ElementsTrait;
use Spiral\Reactor\Traits\UsesTrait;

/**
 * Provides ability to render file content.
 */
class FileDeclaration extends Declaration
{
    use UsesTrait, CommentTrait, ElementsTrait;

    /**
     * File namespace.
     *
     * @var string
     */
    private $namespace = '';

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
     * @param string $namespace
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
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

        if (!empty($this->uses)) {
            $result .= $this->renderUses($indentLevel) . "\n";
        }

        $result .= $this->renderElements($indentLevel);

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