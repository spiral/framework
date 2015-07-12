<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater\Exporters;

use Spiral\Components\View\Compiler\Processors\Templater\AbstractExporter;

/**
 * Export user defined blocks as tag attributes. Use following pattern: node:attributes[="condition"]
 */
class AttributeExporter extends AbstractExporter
{
    /**
     * Create content with mounted blocks (if any).
     *
     * @return string
     */
    public function mountBlocks()
    {
        if (preg_match_all('/ node:attributes(?:=\"([^\'"]+)\")?/i', $this->content, $matches))
        {
            //We have to sort from longest to shortest
            uasort($matches[0], function ($replaceA, $replaceB)
            {
                return strlen($replaceB) - strlen($replaceA);
            });

            foreach ($matches[0] as $id => $replace)
            {
                $blocks = [];
                foreach ($this->getBlocks($matches[1][$id]) as $name => $value)
                {
                    $blocks[$name] = $name . '="' . $value . '"';
                }

                $this->content = str_replace(
                    $replace,
                    $blocks ? ' ' . join(' ', $blocks) : '',
                    $this->content
                );
            }
        }

        return $this->content;
    }
}