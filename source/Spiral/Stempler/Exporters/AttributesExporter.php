<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Stempler\Exporters;

use Spiral\Stempler\ConditionalExporter;

/**
 * Export user defined (outer) blocks as tag attributes.
 *
 * Use following pattern: node:attributes[="condition"]
 */
class AttributesExporter extends ConditionalExporter
{
    /**
     * {@inheritdoc}
     */
    public function mountBlocks($content, array $blocks)
    {
        if (preg_match_all('/ node:attributes(?:=\"([^\'"]+)\")?/i', $content, $matches)) {
            //We have to sort from longest to shortest
            uasort($matches[0], function ($replaceA, $replaceB) {
                return strlen($replaceB) - strlen($replaceA);
            });

            foreach ($matches[0] as $id => $replace) {
                $inject = [];

                //That's why we need longest first (prefix mode)
                foreach ($this->filterBlocks($matches[1][$id], $blocks) as $name => $value) {
                    if ($value === null) {
                        $inject[$name] = $name;
                        continue;
                    }

                    $inject[$name] = $name . '="' . $value . '"';
                }

                //Injecting
                $content = str_replace($replace, $inject ? ' ' . join(' ', $inject) : '', $content);
            }
        }

        return $content;
    }
}