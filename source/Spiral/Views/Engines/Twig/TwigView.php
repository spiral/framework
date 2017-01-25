<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views\Engines\Twig;

use Spiral\Views\Exceptions\RenderException;
use Spiral\Views\ViewInterface;

/**
 * Twig Template with ViewInterface being added.
 */
abstract class TwigView extends \Twig_Template implements ViewInterface
{
    /**
     * @param array $context
     *
     * @return string
     * @throws \Exception
     */
    public function render(array $context = []): string
    {
        try {
            return parent::render($context);
        } catch (\Throwable $e) {
            throw new RenderException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
