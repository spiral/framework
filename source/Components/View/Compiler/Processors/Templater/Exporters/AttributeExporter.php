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
 * Export user defined blocks as tag attributes.
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
        //TODO: CHANGE IT, ADD MORE CLASSES

        if (preg_match_all(
            '/ node:attributes(=[\'"]'
            . '(?:include:(?P<include>[a-z_\-,]+))?\|?'
            . '(?:exclude:(?P<exclude>[a-z_\-,]+))?[\'"])?/i',
            $this->content,
            $matches
        ))
        {
            foreach ($matches[0] as $id => $replace)
            {
                //$include = $matches['include'][$id] ? explode(',', $matches['include'][$id]) : [];
                //$exclude = $matches['exclude'][$id] ? explode(',', $matches['exclude'][$id]) : [];

                //Rendering (yes, we can render this part during collecting, 5 lines to top), but i
                //want to do it like this, cos it will be more flexible to add more features in future

                $blocks = [];
                foreach ($this->blocks as $name => $value)
                {
                    $blocks[$name] = $name . '="' . $value . '"';
                }

                $this->content = str_replace($replace, $blocks ? ' ' . join(' ', $blocks) : '', $this->content);
            }
        }

        return $this->content;
    }
}