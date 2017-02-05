<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views\Engines\Twig;

use Spiral\Views\ViewInterface;

/**
 * Twig Template with ViewInterface being added.
 */
class TwigView implements ViewInterface
{
    /**
     * @var \Twig_TemplateWrapper
     */
    private $wrapper;

    /**
     * @param \Twig_TemplateWrapper $wrapper
     */
    public function __construct(\Twig_TemplateWrapper $wrapper)
    {
        $this->wrapper = $wrapper;
    }

    /**
     * @param array $context
     *
     * @return string
     */
    public function render(array $context = []): string
    {
        return $this->wrapper->render($context);
    }
}
