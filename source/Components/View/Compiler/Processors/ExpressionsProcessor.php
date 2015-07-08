<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors;

use Spiral\Components\View\Compiler\Compiler;
use Spiral\Components\View\Compiler\ProcessorInterface;
use Spiral\Components\View\ViewManager;

class ExpressionsProcessor implements ProcessorInterface
{
    /**
     * Expressions to be replaced.
     *
     * @var array
     */
    protected $expressions = [
        'dependency' => [
            'pattern'  => '/@\\{(?P<name>[a-z0-9_\\.\\-]+)(?: *\\| *(?P<default>[^}]+))?}/i',
            'callback' => ['self', 'dependency']
        ]
    ];

    /**
     * New processors instance with options specified in view config.
     *
     * @param ViewManager $viewManager
     * @param Compiler    $compiler SpiralCompiler instance.
     * @param array       $options
     */
    public function __construct(ViewManager $viewManager, Compiler $compiler, array $options)
    {
        $this->viewManager = $viewManager;

        if (!empty($options['expressions']))
        {
            $this->expressions = $options['expressions'] + $this->expressions;
        }
    }

    /**
     * Performs view code pre-processing. LayeredCompiler will provide view source into processors,
     * processors can perform any source manipulations using this code expect final rendering.
     *
     * @param string $source View source (code).
     * @return string
     */
    public function process($source)
    {
        foreach ($this->expressions as $expression)
        {
            $source = preg_replace_callback(
                $expression['pattern'],
                $expression['callback'],
                $source
            );
        }

        return $source;
    }

    /**
     * Embedded replacer used to set static variable or it's default value.
     *
     * @param array $matches
     * @return string
     */
    public function dependency(array $matches)
    {
        return $this->viewManager->getDependency(
            $matches['name'],
            !empty($matches['default']) ? $matches['default'] : ''
        );
    }
}