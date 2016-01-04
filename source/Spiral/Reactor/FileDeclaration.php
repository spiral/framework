<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor;

use Spiral\Reactor\Body\DocComment;
use Spiral\Reactor\Body\Source;
use Spiral\Reactor\Prototypes\Declaration;
use Spiral\Reactor\Traits\CommentTrait;
use Spiral\Reactor\Traits\UsesTrait;

/**
 * Provides ability to render file content.
 */
class FileDeclaration extends Declaration implements ReplaceableInterface
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
     * @param ClassDeclaration $class
     * @return $this
     */
    public function addClass(ClassDeclaration $class)
    {
        return $this->addElement($class);
    }

    /**
     * Method will automatically mount requested uses is any.
     *
     * @param RenderableInterface $element
     * @return $this
     * @throws Exceptions\ReactorException
     */
    public function addElement(RenderableInterface $element)
    {
        $this->elements->add($element);
        if ($element instanceof DependedInterface) {
            $this->addUses($element->getDependencies());
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function replace($search, $replace)
    {
        $this->docComment->replace($search, $replace);
        $this->elements->replace($search, $replace);

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
            $result .= "namespace {$this->namespace};\n\n";
        }

        if (!empty($this->uses)) {
            $result .= $this->renderUses($indentLevel) . "\n\n";
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
     * @return DeclarationAggregator|ClassDeclaration[]|NamespaceDeclaration[]|Source[]|DocComment[]
     */
    public function elements()
    {
        return $this->elements;
    }
}