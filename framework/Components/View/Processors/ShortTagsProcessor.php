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
use Spiral\Components\View\LayeredCompiler;
use Spiral\Components\View\ProcessorInterface;
use Spiral\Components\View\ViewManager;

class ShortTagsProcessor implements ProcessorInterface
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
     * @param LayeredCompiler $compiler Compiler instance.
     * @param array           $options
     * @param Isolator        $isolator
     */
    public function __construct(LayeredCompiler $compiler, array $options, Isolator $isolator = null)
    {
        $this->isolator = $isolator;
    }

    /**
     * Performs view code pre-processing. View component will provide view source into processors,
     * processors can perform any source manipulations using this code expect final rendering.
     *
     * Will convert short php tags to their longer representation, this will allow spiral views work
     * even in environment with disabled short_tag_open.
     *
     * @param string $source    View source (code).
     * @param string $namespace View namespace.
     * @param string $view      View name.
     * @param string $input     Input filename (usually real view file).
     * @param string $output    Output filename (usually view cache, target file may not exists).
     * @return string
     */
    public function processSource($source, $namespace, $view, $input = '', $output = '')
    {
        //All blocks will be isolated at this moment
        $source = $this->isolator->shortTags(true)->isolatePHP($source);

        $phpBlocks = $this->isolator->getBlocks();

        foreach ($phpBlocks as &$phpBlock)
        {
            if (preg_match('/^<\?(?!php|=)/', $phpBlock))
            {
                $phpBlock = '<?php ' . substr($phpBlock, 2);
            }

            unset($phpBlock);
        }

        //Restoring php blocks after their repairing
        return $this->isolator->setBlocks($phpBlocks)->repairPHP($source);
    }
}