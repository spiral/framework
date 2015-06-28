<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Processors;

use Spiral\Components\Files\FileManager;
use Spiral\Components\Tokenizer\Isolator;
use Spiral\Components\View\ProcessorInterface;
use Spiral\Components\View\LayeredCompiler;
use Spiral\Components\View\ViewManager;

class EvaluateProcessor implements ProcessorInterface
{
    /**
     * List of flag used to detect that block has to be evaluated.
     *
     * @var array
     */
    protected $options = [
        'flags' => [
            '/*compile*/', '#compile', '#php-compile'
        ]
    ];

    /**
     * View manager component.
     *
     * @var ViewManager
     */
    protected $viewManager = null;

    /**
     * File component.
     *
     * @var FileManager
     */
    protected $file = null;

    /**
     * PHP blocs isolator.
     *
     * @var Isolator
     */
    protected $isolator = null;

    /**
     * New processors instance with options specified in view config.
     *
     * @param ViewManager     $viewManager
     * @param LayeredCompiler $compiler Compiler instance.
     * @param array           $options
     * @param FileManager     $file
     * @param Isolator        $isolator
     */
    public function __construct(
        ViewManager $viewManager,
        LayeredCompiler $compiler,
        array $options,
        FileManager $file = null,
        Isolator $isolator = null
    )
    {
        $this->viewManager = $viewManager;
        $this->file = $file;

        $this->options = $options + $this->options;
        $this->isolator = $isolator->shortTags(true);
    }

    /**
     * Performs view code pre-processing. LayeredCompiler will provide view source into processors,
     * processors can perform any source manipulations using this code expect final rendering.
     *
     * All php blocks with included compilation flag will be rendered as this stage.
     *
     * @param string $source    View source (code).
     * @param string $namespace View namespace.
     * @param string $view      View name.
     * @param string $input     Input filename (usually real view file).
     * @param string $output    Output filename (usually view cache, target file may not exists).
     * @return string
     * @throws \ErrorException
     */
    public function processSource($source, $namespace, $view, $input = '', $output = '')
    {
        //Real php source code isolation
        $source = $this->isolator->isolatePHP($source);

        //Restoring only evaluator blocks
        $evaluatorBlocks = [];
        $phpBlocks = [];

        foreach ($this->isolator->getBlocks() as $id => $phpBlock)
        {
            foreach ($this->options['flags'] as $flag)
            {
                if (strpos($phpBlock, $flag) !== false)
                {
                    $evaluatorBlocks[$id] = $phpBlock;

                    continue 2;
                }
            }

            $phpBlocks[$id] = $phpBlock;
        }

        $source = $this->isolator->setBlocks($evaluatorBlocks)->repairPHP($source);
        $this->isolator->setBlocks($phpBlocks);

        //We can use eval() but with temp file error handling will be more complete
        $filename = $this->viewManager->cachedFilename($namespace, $view . '-evaluator');
        $this->file->write($filename, $source, FileManager::RUNTIME, true);

        try
        {
            ob_start();
            require_once $filename;
            $source = ob_get_clean();
        }
        catch (\ErrorException $exception)
        {
            throw $exception;
        }

        $this->file->delete($filename);

        //Let's back php source
        return $this->isolator->repairPHP($source);
    }
}