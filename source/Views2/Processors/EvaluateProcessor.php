<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors;

use Spiral\Components\Files\FileManager;
use Spiral\Components\Tokenizer\Isolator;
use Spiral\Components\View\Compiler\Compiler;
use Spiral\Components\View\Compiler\ProcessorInterface;
use Spiral\Components\View\ViewManager;

class EvaluateProcessor implements ProcessorInterface
{
    /**
     * ViewManager component.
     *
     * @var ViewManager
     */
    protected $viewManager = null;

    /**
     * Active compiler.
     *
     * @var Compiler
     */
    protected $compiler = null;

    /**
     * FileManager component.
     *
     * @var FileManager
     */
    protected $file = null;

    /**
     * Processor options.
     *
     * @var array
     */
    protected $options = [
        'flags' => [
            '/*compile*/', '#compile', '#php-compile'
        ]
    ];

    /**
     * New processors instance with options specified in view config.
     *
     * @param ViewManager $viewManager
     * @param Compiler    $compiler SpiralCompiler instance.
     * @param array       $options
     * @param FileManager $file
     */
    public function __construct(
        ViewManager $viewManager,
        Compiler $compiler,
        array $options,
        FileManager $file = null
    )
    {
        $this->viewManager = $viewManager;
        $this->compiler = $compiler;
        $this->options = $options + $this->options;

        $this->file = !empty($file) ? $file : FileManager::getInstance(
            $this->viewManager->getContainer()
        );
    }

    /**
     * Performs view code pre-processing. LayeredCompiler will provide view source into processors,
     * processors can perform any source manipulations using this code expect final rendering.
     *
     * @param string   $source   View source (code).
     * @param Isolator $isolator PHP isolator instance.
     * @return string
     * @throws \ErrorException
     */
    public function process($source, Isolator $isolator = null)
    {
        $isolator = !empty($isolator) ? $isolator : new Isolator();

        //Real php source code isolation
        $source = $isolator->isolatePHP($source);

        //Restoring only evaluator blocks
        $evaluatorBlocks = [];
        $phpBlocks = [];

        foreach ($isolator->getBlocks() as $id => $phpBlock)
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

        $source = $isolator->setBlocks($evaluatorBlocks)->repairPHP($source);
        $isolator->setBlocks($phpBlocks);

        $filename = $this->viewManager->cacheFilename(
            $this->compiler->getNamespace(),
            $this->compiler->getView() . '-evaluator-' . spl_object_hash($this)
        );

        try
        {
            $this->file->write($filename, $source, FileManager::RUNTIME, true);

            ob_start();
            require_once $filename;
            $source = ob_get_clean();

            $this->file->delete($filename);
        }
        catch (\ErrorException $exception)
        {
            throw $exception;
        }

        return $isolator->repairPHP($source);
    }

    /**
     * Extract php source from php block (no echos). Used to convert php blocks provided by templater
     * to local variables.
     *
     * @param string $phpBlock
     * @return string
     */
    static public function fetchPHP($phpBlock)
    {
        if (strpos($phpBlock, '<?') !== 0)
        {
            return var_export($phpBlock, true);
        }

        $phpBlock = trim(substr($phpBlock, 2, -2));
        if (substr($phpBlock, 0, 3) == 'php')
        {
            $phpBlock = trim(substr($phpBlock, 3));
        }

        if (substr($phpBlock, 0, 1) == '=')
        {
            $phpBlock = substr($phpBlock, 1);
        }

        if (substr($phpBlock, 0, 4) == 'echo')
        {
            $phpBlock = substr($phpBlock, 4);
        }

        return trim($phpBlock, '; ');
    }
}