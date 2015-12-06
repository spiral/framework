<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor;

use Spiral\Reactor\Body\Source;
use Spiral\Reactor\Exceptions\ReactorException;
use Spiral\Reactor\Prototypes\Declaration;
use Spiral\Reactor\Traits\CommentTrait;
use Spiral\Reactor\Traits\UsesTrait;

/**
 * Provides ability to render file content.
 *
 * @property DeclarationAggregator|ClassDeclaration[]|NamespaceDeclaration[]|Source[]|DocComment[]
 *           $elements
 * @property DocComment
 *           $comment
 */
class FileDeclaration extends Declaration
{
    use UsesTrait, CommentTrait;

    /**
     * File namespace.
     *
     * @var string
     */
    private $namespace = '';

    /**
     * @var DeclarationAggregator
     */
    private $elements = null;

    /**
     * @param string $namespace
     * @param string $comment
     */
    public function __construct($namespace = '', $comment = '')
    {
        $this->namespace = $namespace;
        $this->docComment = new DocComment();

        //todo: Function declaration as well.
        $this->elements = new DeclarationAggregator([
            ClassDeclaration::class,
            NamespaceDeclaration::class,
            DocComment::class,
            Source::class
        ]);

        $this->initComment($comment);
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

        $result .= $this->elements->render($indentLevel);

        return $result;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render(0);
    }

    /**
     * @return DeclarationAggregator
     */
    public function elements()
    {
        return $this->elements;
    }

    /**
     * Returns aggregator for property name elements.
     *
     * @todo DRY
     * @param string $name
     * @return mixed
     * @throws ReactorException
     */
    public function __get($name)
    {
        if ($name == 'elements') {
            return $this->elements();
        }

        if ($name == 'comment') {
            return $this->comment();
        }

        throw new ReactorException("Undefined property '{$name}'.");
    }
}