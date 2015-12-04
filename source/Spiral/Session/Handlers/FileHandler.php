<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Session\Handlers;

use Spiral\Core\Component;
use Spiral\Core\Traits\SaturateTrait;
use Spiral\Files\FilesInterface;

/**
 * Stores session data in file.
 */
class FileHandler extends Component implements \SessionHandlerInterface
{
    /**
     * Additional constructor arguments.
     */
    use SaturateTrait;

    /**
     * @var string
     */
    protected $location = '';

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @param array $options Session handler options.
     * @param int $lifetime Default session lifetime.
     * @param FilesInterface $files
     */
    public function __construct(
        array $options,
        $lifetime = 0,
        FilesInterface $files = null
    )
    {
        $this->location = $options['directory'];

        //Global container as fallback
        $this->files = $this->saturate($files, FilesInterface::class);
    }

    /**
     * @param FilesInterface $files
     */
    public function init(FilesInterface $files)
    {
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($session_id)
    {
        return $this->files->delete($this->getFilename($session_id));
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        foreach ($this->files->getFiles($this->location) as $filename) {
            if ($this->files->time($filename) < time() - $maxlifetime) {
                $this->files->delete($filename);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function open($save_path, $session_id)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($session_id)
    {
        return $this->files->exists($this->getFilename($session_id))
            ? $this->files->read($this->getFilename($session_id))
            : false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($session_id, $session_data)
    {
        return $this->files->write(
            $this->getFilename($session_id),
            $session_data,
            FilesInterface::RUNTIME,
            true
        );
    }

    /**
     * Session data filename.
     *
     * @param string $session_id
     * @return string
     */
    protected function getFilename($session_id)
    {
        return $this->location . FilesInterface::SEPARATOR . $session_id;
    }
}
