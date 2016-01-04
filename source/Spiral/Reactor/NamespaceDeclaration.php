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
use Spiral\Reactor\Prototypes\NamedDeclaration;
use Spiral\Reactor\Traits\CommentTrait;
use Spiral\Reactor\Traits\UsesTrait;

/**
 * Represent namespace declaration. Attention, namespace renders in a form of namespace name { ... }
 */
class NamespaceDeclaration extends NamedDeclaration implements ReplaceableInterface
{
    use UsesTrait, CommentTrait;

    /**
     * @var DeclarationAggregator
     */
    private $elements = null;

    /**
     * @param string $name
     * @param string $comment
     */
    public function __construct($name = '', $comment = '')
    {
        parent::__construct($name);

        //todo: Function declaration
        $this->elements = new DeclarationAggregator([
            ClassDeclaration::class,
            DocComment::class,
            Source::class
        ]);

        $this->initComment($comment);
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
     * @todo DRY, see FileDeclaration
     * @param RenderableInterface $element
     * @return $this
     * @throws Exceptions\ReactorException
     */
    public function addElement(RenderableInterface $element)
    {
        $this->elements->add($element);
        if ($element instanceof UsesRequesterInterface) {
            $this->addUses($element->requestsUses());
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
        $result = '';
        $indentShift = 0;

        if (!$this->docComment->isEmpty()) {
            $result .= $this->docComment->render($indentLevel) . "\n";
        }

        if (!empty($this->getName())) {
            $result = $this->indent("namespace {$this->getName()} {", $indentLevel) . "\n";
            $indentShift = 1;
        }

        if (!empty($this->uses)) {
            $result .= $this->renderUses($indentLevel + $indentShift) . "\n\n";
        }

        $result .= $this->elements->render($indentLevel + $indentShift);

        if (!empty($this->getName())) {
            $result .= "\n" . $this->indent("}", $indentLevel);
        }

        return $result;
    }

    /**
     * @return DeclarationAggregator|ClassDeclaration[]|Source[]|DocComment[]
     */
    public function elements()
    {
        return $this->elements;
    }
}