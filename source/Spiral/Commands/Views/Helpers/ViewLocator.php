<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views;

use Spiral\Core\Component;
use Spiral\Files\FilesInterface;
use Spiral\Views\Configs\ViewsConfig;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Helper class used to locate all possible views.
 */
class ViewLocator extends Component
{
    /**
     * @var ViewsConfig
     */
    protected $config = null;

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @param ViewsConfig    $config
     * @param FilesInterface $files
     */
    public function __construct(ViewsConfig $config, FilesInterface $files)
    {
        $this->config = $config;
        $this->files = $files;
    }

    /**
     * Available namespaces.
     *
     * @return array
     */
    public function getNamespaces(): array
    {
        return array_keys($this->config->getNamespaces());
    }

    /**
     * Find all available views for given namespace, view name associated with it's engine.
     *
     * @param string $namespace
     *
     * @return array
     */
    public function getViews(string $namespace): array
    {
        $result = [];
        foreach ($this->config->namespaceDirectories($namespace) as $directory) {
            foreach ($this->config->getEngines() as $engine) {
                $extension = $this->config->engineExtension($engine);

                $finder = new Finder();
                foreach ($finder->in($directory)->name("*.{$extension}") as $file) {
                    /**
                     * @var SplFileInfo $file
                     */

                    if (isset($result[$file->getRelativePathname()])) {
                        continue;
                    }

                    $result[$file->getRelativePathname()] = $engine;
                }
            }
        }

        return $result;
    }
}