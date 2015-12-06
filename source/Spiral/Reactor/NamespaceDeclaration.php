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
use Spiral\Reactor\Prototypes\NamedDeclaration;
use Spiral\Reactor\Traits\CommentTrait;
use Spiral\Reactor\Traits\UsesTrait;

/**
 * Represent namespace declaration. Attention, namespace renders in a form of namespace name { ... }
 */
class NamespaceDeclaration extends NamedDeclaration
{
    use UsesTrait, CommentTrait;

    /**
     * @var DeclarationAggregator
     */
    private $elements = null;

    /**
     * @param string $name
     */
    public function __construct($name = '')
    {
        parent::__construct($name);

        //todo: Function declaration
        $this->elements = new DeclarationAggregator([
            ClassDeclaration::class,
            DocComment::class,
            Source::class
        ]);
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
            $result = $this->indent("namespace {$this->getName()} {", $indentLevel);
            $indentShift = 1;
        }

        if (!empty($this->uses)) {
            $result .= $this->renderUses($indentLevel + $indentShift) . "\n";
        }

        $result .= $this->elements->render($indentLevel + $indentShift);

        if (!empty($this->getName())) {
            $result = $this->indent("}", $indentLevel);
        }

        return $result;
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