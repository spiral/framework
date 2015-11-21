<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Stempler;

/**
 * Exported has to export (obviously) specified blocks into content. Every exporter should
 * define it's own pattern to initiate export.
 *
 * Most of spiral exporters will provide ability to specify list of elements to be included or
 * excluded from exporting. In addition you can use custom prefix for some of your elements.
 *
 * Pattern: include:element,elementB;exclude:elementC,elementD,patten-*;prefix:my-prefix;
 *
 * Prefix will allow you to match some attributes to specific spot, use exclude pattern (with star)
 * to remove attributes like that from other places.
 */
abstract class ConditionalExporter implements ExporterInterface
{
    /**
     * Get blocks matching specified condition.
     *
     * @param string $condition
     * @param array  $blocks
     * @return array
     */
    protected function filterBlocks($condition = null, array $blocks)
    {
        if (empty($condition)) {
            return $blocks;
        }

        $conditions = [];
        foreach (explode(';', $condition) as $condition) {
            if (strpos($condition, ':') === false) {
                //Invalid
                continue;
            }

            list($name, $value) = explode(":", $condition);
            $conditions[$name] = $value;
        }

        $result = $blocks;
        if (isset($conditions['prefix'])) {
            $result = [];
            foreach ($blocks as $name => $value) {
                if (strpos($name, $conditions['prefix']) === 0) {
                    //Prefix plus "-" sign
                    $result[substr($name, strlen($conditions['prefix']) + 1)] = $value;
                }
            }
        }

        if (isset($conditions['include'])) {
            $include = explode(',', $conditions['include']);

            foreach ($blocks as $name => $value) {
                if (!in_array($name, $include)) {
                    unset($result[$name]);
                }
            }
        }

        if (isset($conditions['exclude'])) {
            $exclude = explode(',', $conditions['exclude']);

            foreach ($blocks as $name => $value) {
                if (in_array($name, $exclude)) {
                    unset($result[$name]);
                }

                foreach ($exclude as $pattern) {
                    if (strpos($pattern, '*') === false) {
                        //Not pattern
                        continue;
                    }

                    $pattern = '/^' . str_replace('*', '.+', $pattern) . '/i';

                    if (preg_match($pattern, $name)) {
                        unset($result[$name]);
                    }
                }
            }
        }

        return $result;
    }
}