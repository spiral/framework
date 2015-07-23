<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors;

use Spiral\Components\Tokenizer\Isolator;
use Spiral\Components\View\Compiler\Compiler;
use Spiral\Components\View\Compiler\ProcessorInterface;
use Spiral\Components\View\ViewManager;
use Spiral\Helpers\StringHelper;
use Spiral\Support\Html\Tokenizer;

class PrettifyProcessor implements ProcessorInterface
{
    /**
     * Prettify-options.
     *
     * @var array
     */
    protected $options = [
        'normalizeEndings' => true
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
        $this->options = $options + $this->options;
    }

    /**
     * Performs view code pre-processing. LayeredCompiler will provide view source into processors,
     * processors can perform any source manipulations using this code expect final rendering.
     *
     * @param string $source View source (code).
     * @return string
     * @throws \ErrorException
     */
    public function process($source)
    {
        if ($this->options['normalizeEndings'])
        {
            $source = $this->normalizeEndings($source);
        }

        return $source;
    }

    /**
     * Remove blank lines.
     *
     * @param string $source
     * @return string
     */
    protected function normalizeEndings($source)
    {
        $isolator = new Isolator();

        //Step #1, \n only
        $source = $isolator->isolatePHP(
            StringHelper::normalizeEndings($source)
        );

        //Step #2, chunking by lines
        $sourceLines = explode("\n", $source);

        //Step #3, no blank lines and html comments (will keep conditional commends)
        $sourceLines = array_filter($sourceLines, function ($line)
        {
            return trim($line);
        });

        return $isolator->repairPHP(join("\n", $sourceLines));
    }
}