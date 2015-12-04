<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views;

use Spiral\Core\Component;
use Spiral\Core\Traits\SaturateTrait;
use Spiral\Files\FilesInterface;
use Spiral\Views\Exceptions\LoaderException;

/**
 * Default views loader is very similar to twig loader (compatible), however it uses different viwe
 * namespace syntax, can change it's default namespace and force specified file extension. Plus it
 * works over FilesInterface.
 */
class ViewLoader extends Component implements LoaderInterface
{
    /**
     * Saturable.
     */
    use SaturateTrait;

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
     * View cache. Can be improved using HippocampusInterface.
     *
     * @var array
     */
    protected $cache = [];

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @param array $namespaces
     * @param FilesInterface|null $files
     */
    public function __construct(array $namespaces, FilesInterface $files = null)
    {
        $this->namespaces = $namespaces;

        $this->files = $this->saturate($files, FilesInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource($path)
    {
        return $this->files->read($this->findView($path));
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        return $this->findView($name);
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time)
    {
        return $this->files->time($this->findView($name)) <= $time;
    }

    /**
     * {@inheritdoc}
     */
    public function localFilename($path)
    {
        return $this->findView($path);
    }

    /**
     * {@inheritdoc}
     */
    public function viewNamespace($path)
    {
        $this->findView($path);

        return $this->cache[$path][1];
    }

    /**
     * {@inheritdoc}
     */
    public function viewName($path)
    {
        $this->findView($path);

        return $this->cache[$path][2];
    }

    /**
     * {@inheritdoc}
     */
    public function withExtension($extension)
    {
        $loader = clone $this;
        $loader->extension = $extension;

        return $loader->flushCache();
    }

    /**
     * Locate view filename based on current loader settings.
     *
     * @param string $path
     * @return string
     * @throws LoaderException
     */
    protected function findView($path)
    {
        if (isset($this->cache[$path])) {
            return $this->cache[$path][0];
        }

        $this->validateName($path);
        list($namespace, $filename) = $this->parsePath($path);

        if (!isset($this->namespaces[$namespace])) {
            throw new LoaderException("Undefined view namespace '{$namespace}'.");
        }

        foreach ($this->namespaces[$namespace] as $directory) {
            if ($this->files->exists($directory . $filename)) {

                $this->cache[$path] = [
                    $directory . $filename,
                    $namespace,
                    $this->resolveName($filename)
                ];

                return $this->cache[$path][0];
            }
        }

        throw new LoaderException("Unable to locate view '{$filename}' in namespace '{$namespace}'.");
    }

    /**
     * Fetch namespace and filename from view name or force default values.
     *
     * @param string $path
     * @return array
     * @throws LoaderException
     */
    protected function parsePath($path)
    {
        //Cutting extra symbols (see Twig)
        $filename = preg_replace('#/{2,}#', '/', str_replace('\\', '/', (string)$path));

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
     * @throws LoaderException
     */
    protected function validateName($name)
    {
        if (false !== strpos($name, "\0")) {
            throw new LoaderException('A template name cannot contain NUL bytes.');
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
                    'Looks like you try to load a template outside configured directories (%s).',
                    $name
                ));
            }
        }
    }

    /**
     * Flushing loading cache.
     *
     * @return $this
     */
    protected function flushCache()
    {
        $this->cache = [];

        return $this;
    }

    /**
     * Resolve view name based on filename (depends on current extension settings).
     *
     * @param string $filename
     * @return string
     */
    private function resolveName($filename)
    {
        if (empty($this->extension)) {
            return $filename;
        }

        return substr($filename, 0, -1 * (1 + strlen($this->extension)));
    }
}
