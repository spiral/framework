<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views\Processors;

use Spiral\Core\Component;
use Spiral\Core\ContainerInterface;
use Spiral\Files\FilesInterface;
use Spiral\Tokenizer\Isolator;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewSource;

/**
 * Evaluate processor can evaluate php blocks which contain specific flags at compilation phase.
 */
class EvaluateProcessor extends Component implements ProcessorInterface
{
    /**
     * @var array
     */
    protected $flags = ['/*compile*/', '#compile', '#php-compile'];

    /**
     * @var string
     */
    protected $namespace = '';

    /**
     * @var string
     */
    protected $view = '';

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * To be accessible in compilable php code.
     *
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param array              $flags
     * @param FilesInterface     $files
     * @param ContainerInterface $container
     */
    public function __construct(
        array $flags = [],
        FilesInterface $files,
        ContainerInterface $container
    ) {
        if (!empty($flags)) {
            $this->flags = $flags;
        }

        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     *
     * @param Isolator|null $isolator
     */
    public function modify(
        EnvironmentInterface $environment,
        ViewSource $view,
        string $code
    ): string {
        $isolator = new Isolator();

        //Let's hide original php blocks
        $code = $isolator->isolatePHP($code);

        //Now we have to detect blocks which has to be compiled
        //Restoring only evaluator blocks
        $phpBlocks = $evaluateBlocks = [];

        foreach ($isolator->getBlocks() as $id => $phpBlock) {
            if ($this->isTargeted($phpBlock)) {
                $evaluateBlocks[] = $id;
                continue;
            }

            $phpBlocks[] = $id;
        }

        //Restoring evaluate blocks only
        $code = $isolator->repairPHP($code, true, $evaluateBlocks);

        //Let's create temporary filename
        $filename = $this->evalFilename($environment, $view);

        try {
            //Temporary PHP to compile code
            $this->files->write(
                $filename,
                $code,
                FilesInterface::RUNTIME,
                true
            );

            ob_start();
            $__outputLevel__ = ob_get_level();

            //Can be accessed in evaluated php
            $this->namespace = $view->getNamespace();
            $this->view = $view->getName();

            try {
                include_once $this->files->localFilename($filename);
            } finally {
                while (ob_get_level() > $__outputLevel__) {
                    ob_end_clean();
                }
            }

            $this->namespace = '';
            $this->view = '';

            $view = ob_get_clean();

            //Dropping temporary filename
            $this->files->delete($filename);
        } catch (\Throwable $exception) {
            throw $exception;
        }

        //Restoring all original php blocks
        return $isolator->repairPHP($view);
    }

    /**
     * Extract php source from php block (no echos). Used to convert php blocks provided by
     * templater to local variables.
     *
     * Function available in evaluated blocks using $this->fetchPHP($block);
     *
     * @param string $phpBlock
     *
     * @return string
     */
    public function fetchPHP(string $phpBlock): string
    {
        if (strpos($phpBlock, '<?') !== 0) {
            return var_export($phpBlock, true);
        }

        $phpBlock = substr(trim($phpBlock), 2, -2);
        if (substr($phpBlock, 0, 3) == 'php') {
            $phpBlock = trim(substr($phpBlock, 3));
        }

        if (substr($phpBlock, 0, 1) == '=') {
            $phpBlock = substr($phpBlock, 1);
        }

        if (substr($phpBlock, 0, 4) == 'echo') {
            $phpBlock = substr($phpBlock, 4);
        }

        return trim($phpBlock, '; ');
    }

    /**
     * Check if php block has to be compiled.
     *
     * @param string $block
     *
     * @return bool
     */
    protected function isTargeted(string $block): bool
    {
        foreach ($this->flags as $flag) {
            if (strpos($block, $flag) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Unique filename to be used for compilation.
     *
     * @param EnvironmentInterface $environment
     * @param  ViewSource          $view
     *
     * @return string
     */
    private function evalFilename(
        EnvironmentInterface $environment,
        ViewSource $view
    ): string {
        $filename = "{$view->getNamespace()}.{$view->getName()}.eval." . spl_object_hash($this) . '.php';

        return $environment->cacheDirectory() . $filename;
    }
}