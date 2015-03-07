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
use Spiral\Components\View\View;

class EvaluateProcessor implements ProcessorInterface
{
    /**
     * Evaluator tags, ASP tags by default.
     *
     * @var array
     */
    protected $options = array(
        'tagOpen'  => '<%',
        'tagClose' => '%>'
    );

    /**
     * View component instance.
     *
     * @var View
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
     * @param View        $view View component instance (if presented).
     * @param FileManager $file FileManager component.
     * @param Isolator    $isolator
     */
    public function __construct(array $options, View $view = null, FileManager $file = null, Isolator $isolator = null)
    {
        $this->options = $options + $this->options;
        $this->view = $view;
        $this->file = $file;

        $this->isolator = $isolator->shortTags(true)->aspTags(true);
    }

    /**
     * Performs view code pre-processing. View component will provide view source into processors, processors can perform
     * any source manipulations using this code expect final rendering. Will run all php code in ASP tags during pre-rendering
     * stage. This is "caching" php.
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
            if (substr($phpBlock, 0, strlen($this->options['tagOpen'])) == $this->options['tagOpen'])
            {
                $phpBlock = trim($phpBlock);

                $phpBlock = '<?' . substr($phpBlock, strlen($this->options['tagOpen']), -1 * strlen($this->options['tagClose'])) . '?>';

                if (substr($phpBlock, 0, 3) == '<?=')
                {
                    $phpBlock = '<?php echo ' . ltrim(substr($phpBlock, 3));
                }

                if (preg_match('/^<\?(?!php)/', $phpBlock))
                {
                    $phpBlock = '<?php ' . substr($phpBlock, 2);
                }

                $evaluatorBlocks[$id] = $phpBlock;

                continue;
            }

            $phpBlocks[$id] = $phpBlock;
        }

        $source = $this->isolator->setBlocks($evaluatorBlocks)->repairPHP($source);
        $this->isolator->setBlocks($phpBlocks);

        //We can use eval() but with temp file error handling will be more complete
        $evaluatorFilename = $this->view->cachedFilename($namespace, $view . '-evaluator');
        $this->file->write($evaluatorFilename, $source, FileManager::RUNTIME, true);

        try
        {
            ob_start();
            require_once $evaluatorFilename;
            $source = ob_get_clean();
        }
        catch (\ErrorException $exception)
        {
            throw $exception;
        }

        $this->file->remove($evaluatorFilename);

        //Let's back php source
        return $this->isolator->repairPHP($source);
    }
}