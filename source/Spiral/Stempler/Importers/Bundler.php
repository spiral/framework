<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Stempler\Importers;

use Spiral\Stempler\ImporterInterface;
use Spiral\Stempler\Supervisor;

/**
 * Share all template importers with given supervisor.
 */
class Bundler implements ImporterInterface
{
    /**
     * Importers fetched from bundle file.
     *
     * @var ImporterInterface[]
     */
    protected $importers = [];

    /**
     * @param Supervisor $supervisor
     * @param string     $path
     * @param array      $token
     */
    public function __construct(Supervisor $supervisor, $path, array $token = [])
    {
        $node = $supervisor->createNode($path, $token);
        $supervisor = $node->supervisor();

        if ($supervisor instanceof Supervisor) {
            $this->importers = $supervisor->getImporters();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function importable($element, array $token)
    {
        foreach ($this->importers as $importer) {
            if ($importer->importable($element, $token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function resolvePath($element, array $token)
    {
        foreach ($this->importers as $importer) {
            if ($importer->importable($element, $token)) {
                return $importer->resolvePath($element, $token);
            }
        }

        return null;
    }
}