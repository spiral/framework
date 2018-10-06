<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Twig;

use Spiral\Views\ViewInterface;
use Twig\TemplateWrapper;

class TwigView implements ViewInterface
{
    /** @var TemplateWrapper */
    private $wrapper;

    /**
     * @param TemplateWrapper $wrapper
     */
    public function __construct(TemplateWrapper $wrapper)
    {
        $this->wrapper = $wrapper;
    }

    /**
     * @inheritdoc
     */
    public function render(array $data = []): string
    {
        return $this->wrapper->render($data);
    }
}