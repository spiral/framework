<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Processors;

use Spiral\Components\Tokenizer\Isolator;
use Spiral\Components\View\ProcessorInterface;
use Spiral\Components\View\ViewManager;
use Spiral\Helpers\StringHelper;

class PrettyPrintProcessor implements ProcessorInterface
{
    /**
     * PHP Blocks isolator.
     *
     * @var Isolator
     */
    protected $isolator = null;

    /**
     * New processors instance with options specified in view config.
     *
     * @param array       $options
     * @param ViewManager $view View component instance (if presented).
     * @param Isolator    $isolator
     */
    public function __construct(array $options, ViewManager $view = null, Isolator $isolator = null)
    {
        $this->isolator = $isolator;
    }

    /**
     * Clean html of extra lines to optimize it a little, processors can create a lot of empty lines
     * during combining view files, this processor should be called at the end of chain.
     *
     * @param string $source    View source (code).
     * @param string $view      View name.
     * @param string $namespace View namespace.
     * @return string
     */
    public function processSource($source, $view, $namespace)
    {
        //Step #1, \n only
        $source = StringHelper::normalizeEndings($source);

        $source = $this->isolator->isolatePHP($source);

        //Step #2, chunking by lines
        $lines = explode("\n", $source);

        //Step #3, no blank lines and html comments (will keep conditional commends)
        $lines = array_filter($lines, function ($line)
        {
            return trim($line);
        });

        //Step 4, group it together
        $source = join("\n", $lines);

        return $this->isolator->repairPHP($source);
    }
}