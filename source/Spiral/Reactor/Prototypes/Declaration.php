<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\Prototypes;

use Spiral\Core\Component;
use Spiral\Reactor\RenderableInterface;

/**
 * Generic element declaration.
 */
abstract class Declaration extends Component implements RenderableInterface
{
    /**
     * @param string $string
     * @param int    $indent
     * @return string
     */
    protected function indent($string, $indent = 0)
    {
        return str_repeat(self::INDENT, max($indent, 0)) . $string;
    }
}