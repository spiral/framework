<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater;

abstract class AbstractExporter implements ExporterInterface
{
    /**
     * Content where exported blocks should be injected into.
     *
     * @var string
     */
    protected $content = '';

    /**
     * Blocks to be injected.
     *
     * @var string
     */
    protected $blocks = '';

    /**
     * Exported has to export (obviously) specified blocks into content. Every exporter should
     * defined it's own pattern to initiate export. Most of spiral exporters will provide ability
     * to specify list of elements to be included or excluded from exporting. In addition you can
     * use custom prefix for some of your elements.
     *
     * Pattern: include:element,elementB;exclude:elementC,elementD,patten-*;prefix:my-prefix;
     * Prefix will allow you to match some attributes to specific spot, use exclude pattern (with star)
     * to remove attributes like that from other places.
     *
     * Attention: some processors may require Evaluator processor to be executed after Templater.
     *
     * @param string $content
     * @param array  $blocks
     */
    public function __construct($content, array $blocks)
    {
        $this->content = $content;
        $this->blocks = $blocks;
    }

    /**
     * Get blocks matching specified condition.
     *
     * @param string $condition
     * @return array
     */
    protected function getBlocks($condition)
    {
        if (empty($condition))
        {
            return $this->blocks;
        }

        $conditions = [];
        foreach (explode(';', $condition) as $condition)
        {
            if (strpos($condition, ':') === false)
            {
                //Invalid
                continue;
            }

            list($name, $value) = explode(":", $condition);
            $conditions[$name] = $value;
        }

        $blocks = $this->blocks;
        if (isset($conditions['prefix']))
        {
            $blocks = [];
            foreach ($this->blocks as $name => $value)
            {
                if (strpos($name, $conditions['prefix']) === 0)
                {
                    //Prefix plus "-" sign
                    $blocks[substr($name, strlen($conditions['prefix']) + 1)] = $value;
                }
            }
        }

        if (isset($conditions['include']))
        {
            $include = explode(',', $conditions['include']);

            foreach ($blocks as $name => $value)
            {
                if (!in_array($name, $include))
                {
                    unset($blocks[$name]);
                }
            }
        }


        if (isset($conditions['exclude']))
        {
            $exclude = explode(',', $conditions['exclude']);

            foreach ($blocks as $name => $value)
            {
                if (in_array($name, $exclude))
                {
                    unset($blocks[$name]);
                }

                foreach ($exclude as $pattern)
                {
                    if (strpos($pattern, '*') === false)
                    {
                        //Not pattern
                        continue;
                    }

                    $pattern = '/^' . str_replace('*', '.+', $pattern) . '/i';

                    if (preg_match($pattern, $name))
                    {
                        unset($blocks[$name]);
                    }
                }
            }
        }

        return $blocks;
    }
}