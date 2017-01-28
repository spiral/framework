<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views\Loaders;

use Spiral\Core\Component;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Files\FilesInterface;
use Spiral\Views\Exceptions\LoaderException;
use Spiral\Views\LoaderInterface;
use Spiral\Views\SourceContextInterface;
use Spiral\Views\ViewsInterface;
use Spiral\Views\ViewSource;

/**
 * Default views loader is very similar to twig loader (compatible), however it uses different view
 * namespace syntax, can change it's default namespace and force specified file extension. Plus it
 * works over FilesInterface.
 */
class FileLoader extends Component implements LoaderInterface
{
    use BenchmarkTrait;

    /**
     * View cache. Can be improved using MemoryInterface.
     *
     * @var array
     */
    private $sourceCache = [];

    /**
     * Such extensions will automatically be added to every file but only if no other extension
     * specified in view name. As result you are able to render "home" view, instead of "home.twig".
     *
     * @var string|null
     */
    protected $extension = null;

    /**
     * Available view namespaces associated with their directories.
     *
     * @var array
     */
    protected $namespaces = [];

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @param array          $namespaces
     * @param FilesInterface $files
     */
    public function __construct(array $namespaces, FilesInterface $files)
    {
        $this->namespaces = $namespaces;
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * {@inheritdoc}
     */
    public function withExtension(string $extension = null): LoaderInterface
    {
        $loader = clone $this;
        $loader->extension = $extension;

        return $loader->flushCache();
    }

    /**
     * {@inheritdoc}
     */
    public function getSource(string $path): ViewSource
    {
        if (isset($this->sourceCache[$path])) {
            //Already resolved and cached
            return $this->sourceCache[$path];
        }

        //Making sure requested name is valid
        $this->validatePath($path);

        list($namespace, $filename) = $this->parsePath($path);

        if (!isset($this->namespaces[$namespace])) {
            throw new LoaderException("Undefined view namespace '{$namespace}'");
        }

        foreach ($this->namespaces[$namespace] as $directory) {
            //Seeking for view filename
            if ($this->files->exists($directory . $filename)) {

                //Found view context
                $this->sourceCache[$path] = new ViewSource(
                    $directory . $filename,
                    $this->fetchName($filename),
                    $namespace
                );

                return $this->sourceCache[$path];
            }
        }

        throw new LoaderException("Unable to locate view '{$filename}' in namespace '{$namespace}'");
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $path): bool
    {
        if (isset($this->sourceCache[$path])) {
            //Already resolved and cached
            return true;
        }

        try {
            return !empty($this->getSource($path));
        } catch (LoaderException $e) {
            return false;
        }
    }

    /**
     * Fetch namespace and filename from view name or force default values.
     *
     * @param string $path
     *
     * @return array
     * @throws LoaderException
     */
    protected function parsePath(string $path): array
    {
        //Cutting extra symbols (see Twig)
        $filename = preg_replace(
            '#/{2,}#',
            '/',
            str_replace('\\', '/', (string)$path)
        );

        if (strpos($filename, '.') === false && !empty($this->extension)) {
            //Forcing default extension
            $filename .= '.' . $this->extension;
        }

        if (strpos($filename, ViewsInterface::NS_SEPARATOR) !== false) {
            return explode(ViewsInterface::NS_SEPARATOR, $filename);
        }

        //Twig like namespaces
        if (isset($filename[0]) && $filename[0] == '@') {
            if (($separator = strpos($filename, '/')) === false) {
                throw new LoaderException(sprintf(
                    'Malformed namespaced template name "%s" (expecting "@namespace/template_name").',
                    $path
                ));
            }

            $namespace = substr($filename, 1, $separator - 1);
            $filename = substr($filename, $separator + 1);

            return [$namespace, $filename];
        }

        //Let's force default namespace
        return [ViewsInterface::DEFAULT_NAMESPACE, $filename];
    }

    /**
     * Make sure view filename is OK. Same as in twig.
     *
     * @param string $name
     *
     * @throws LoaderException
     */
    protected function validatePath(string $name)
    {
        if (false !== strpos($name, "\0")) {
            throw new LoaderException('A template name cannot contain NUL bytes');
        }

        $name = ltrim($name, '/');
        $parts = explode('/', $name);
        $level = 0;
        foreach ($parts as $part) {
            if ('..' === $part) {
                --$level;
            } elseif ('.' !== $part) {
                ++$level;
            }

            if ($level < 0) {
                throw new LoaderException(sprintf(
                    'Looks like you try to load a template outside configured directories (%s)',
                    $name
                ));
            }
        }
    }

    /**
     * Flushing loading cache.
     *
     * @return self
     */
    protected function flushCache(): FileLoader
    {
        $this->sourceCache = [];

        return $this;
    }

    /**
     * Resolve view name based on filename (depends on current extension settings).
     *
     * @param string $filename
     *
     * @return string
     */
    private function fetchName(string $filename): string
    {
        if (empty($this->extension)) {
            return $filename;
        }

        return substr($filename, 0, -1 * (1 + strlen($this->extension)));
    }
}