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
use Spiral\Components\View\ViewManager;

class EvaluateProcessor implements ProcessorInterface
{
    /**
     * Evaluator tags, ASP tags by default.
     *
     * @var array
     */
    protected $options = array(
        'flags' => array(
            '/*compile*/', '#compile', '#php-compile'
        )
    );

    /**
     * View component instance.
     *
     * @var ViewManager
     */
    protected $view = null;

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
     * @param array       $options
     * @param ViewManager $view View component instance (if presented).
     * @param FileManager $file FileManager component.
     * @param Isolator    $isolator
     */
    public function __construct(
        array $options,
        ViewManager $view = null,
        FileManager $file = null,
        Isolator $isolator = null
    )
    {
        $this->options = $options + $this->options;
        $this->view = $view;
        $this->file = $file;

        $this->isolator = $isolator->shortTags(true);
    }

    /**
     * Performs view code pre-processing. All php blocks with included compilation flag will be
     * rendered as this stage.
     *
     * @param string $source    View source (code).
     * @param string $view      View name.
     * @param string $namespace View namespace.
     * @return string
     * @throws \ErrorException
     */
    public function processSource($source, $view, $namespace)
    {
        //Real php source code isolation
        $source = $this->isolator->isolatePHP($source);

        //Restoring only evaluator blocks
        $evaluatorBlocks = array();
        $phpBlocks = array();

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
        $filename = $this->view->cachedFilename($namespace, $view . '-evaluator');
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

        $this->file->remove($filename);

        //Let's back php source
        return $this->isolator->repairPHP($source);
    }
}