<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\Traits;

use Spiral\Reactor\RenderableInterface;

/**
 * Ability to contain set of elements including classes.
 */
trait ElementsTrait
{
    /**
     * @var RenderableInterface[]
     */
    private $elements = [];

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
     * Get all file elements.
     *
     * @return RenderableInterface[]
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Render all collected elements.
     *
     * @param int $indentLevel
     * @return string
     */
    protected function renderElements($indentLevel = 0)
    {
        $result = '';
        foreach ($this->elements as $element) {
            $result .= $element->render($indentLevel) . "\n";
        }

        return $result;
    }
}