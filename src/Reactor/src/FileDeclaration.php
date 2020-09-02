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
use Spiral\Reactor\Partial\Directives;
use Spiral\Reactor\Partial\Source;
use Spiral\Reactor\Traits\CommentTrait;
use Spiral\Reactor\Traits\UsesTrait;

/**
 * Provides ability to render file content.
 */
class FileDeclaration extends AbstractDeclaration implements ReplaceableInterface
{
    use UsesTrait;
    use CommentTrait;

    /**
     * File namespace.
     *
     * @var string
     */
    private $namespace;

    /** @var Directives|null */
    private $directives;

    /**
     * @var Aggregator
     */
    private $elements;

    /**
     * @param string $namespace
     * @param string $comment
     */
    public function __construct(string $namespace = '', string $comment = '')
    {
        $this->namespace = $namespace;

        $this->elements = new Aggregator([
            ClassDeclaration::class,
            NamespaceDeclaration::class,
            Comment::class,
            Source::class,
        ]);

        $this->initComment($comment);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->render(0);
    }

    /**
     * @param string $namespace
     * @return self
     */
    public function setNamespace(string $namespace): FileDeclaration
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string ...$directives
     * @return FileDeclaration
     */
    public function setDirectives(string ...$directives): FileDeclaration
    {
        $this->directives = new Directives(...$directives);

        return $this;
    }

    /**
     * Method will automatically mount requested uses is any.
     *
     * @param DeclarationInterface $element
     * @return self
     * @throws Exception\ReactorException
     */
    public function addElement(DeclarationInterface $element): FileDeclaration
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
    public function replace($search, $replace): FileDeclaration
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
        $result = "<?php\n";

        if (!$this->docComment->isEmpty()) {
            $result .= $this->docComment->render($indentLevel) . "\n";
        }

        if ($this->directives !== null && !empty($this->directives->render())) {
            $result .= $this->directives->render() . "\n\n";
        }

        if (!empty($this->namespace)) {
            if ($this->docComment->isEmpty()) {
                $result .= "\n";
            }
            $result .= "namespace {$this->namespace};\n\n";
        }

        if (!empty($this->uses)) {
            $result .= $this->renderUses($indentLevel) . "\n\n";
        }

        $result .= $this->elements->render($indentLevel);
        $result .= "\n";

        return $result;
    }

    /**
     * @return Aggregator|ClassDeclaration[]|NamespaceDeclaration[]|Source[]|Comment[]
     */
    public function getElements(): Aggregator
    {
        return $this->elements;
    }
}
