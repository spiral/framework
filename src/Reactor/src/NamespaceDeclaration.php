<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Reactor;

use Spiral\Reactor\Partial\Comment;
use Spiral\Reactor\Partial\Source;
use Spiral\Reactor\Traits\CommentTrait;
use Spiral\Reactor\Traits\NamedTrait;
use Spiral\Reactor\Traits\UsesTrait;

/**
 * Represent namespace declaration. Attention, namespace renders in a form of namespace name { ... }
 */
class NamespaceDeclaration extends AbstractDeclaration implements ReplaceableInterface
{
    use NamedTrait;
    use UsesTrait;
    use CommentTrait;

    /**
     * @var Aggregator
     */
    private $elements;

    /**
     * @param string $name
     * @param string $comment
     */
    public function __construct(string $name = '', string $comment = '')
    {
        $this->setName($name);

        $this->elements = new Aggregator([
            ClassDeclaration::class,
            Comment::class,
            Source::class
        ]);

        $this->initComment($comment);
    }

    /**
     * Method will automatically mount requested uses is any.
     *
     * @param DeclarationInterface $element
     * @return self
     * @throws Exception\ReactorException
     */
    public function addElement(DeclarationInterface $element): NamespaceDeclaration
    {
        $this->elements->add($element);
        if ($element instanceof DependedInterface) {
            $this->addUses($element->getDependencies());
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return self
     */
    public function replace($search, $replace): NamespaceDeclaration
    {
        $this->docComment->replace($search, $replace);
        $this->elements->replace($search, $replace);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render(int $indentLevel = 0): string
    {
        $result = '';
        $indentShift = 0;

        if (!$this->docComment->isEmpty()) {
            $result .= $this->docComment->render($indentLevel) . "\n";
        }

        if (!empty($this->getName())) {
            $result .= $this->addIndent("namespace {$this->getName()} {", $indentLevel) . "\n";
            $indentShift = 1;
        }

        if (!empty($this->uses)) {
            $result .= $this->renderUses($indentLevel + $indentShift) . "\n\n";
        }

        $result .= $this->elements->render($indentLevel + $indentShift);

        if (!empty($this->getName())) {
            $result .= "\n" . $this->addIndent('}', $indentLevel);
        }

        return $result;
    }

    /**
     * @return Aggregator|ClassDeclaration[]|Source[]|Comment[]
     */
    public function getElements(): Aggregator
    {
        return $this->elements;
    }
}
