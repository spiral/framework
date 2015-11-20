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
 * Only wraps Twig template.
 */
abstract class TwigView extends \Twig_Template implements ViewInterface
{
    /**
     * @param array $context
     * @return string
     * @throws \Exception
     */
    public function render(array $context = [])
    {
        return parent::render($context);
    }
}
