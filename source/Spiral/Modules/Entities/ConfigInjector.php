<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Modules\Entities;

use Spiral\Core\Component;
use Spiral\Core\Exceptions\SugarException;
use Spiral\Core\Traits\SaturateTrait;
use Spiral\Debug\Traits\LoggerTrait;
use Spiral\Files\FilesInterface;
use Spiral\Modules\Exceptions\InjectorException;
use Symfony\Component\Process\Process;

/**
 * Provides ability to inject string lines in a specified placeholder of configuration file.
 */
class ConfigInjector extends Component
{
    /**
     * Placeholder regex.
     */
    const PLACEHOLDER_REGEX = '#/\*\{\{([a-z0-9\.\-]+)\}\}\*/#';

    /**
     * @var array
     */
    private $lines = [];

    /**
     * @param string $content
     */
    public function __construct($content)
    {
        $this->lines = explode("\n", $content);
    }

    /**
     * List of all placeholders localed in source lines.
     *
     * @return array
     */
    public function getPlaceholders()
    {
        $placeholders = [];
        foreach ($this->lines as $line) {
            if (preg_match(static::PLACEHOLDER_REGEX, $line, $matches)) {
                $placeholders[] = $matches[1];
            }
        }

        return $placeholders;
    }

    /**
     * Inject configuration lines in location for given placeholder.
     *
     * @param string $placeholder
     * @param string $wrapper Wrapper string must identify what module added configuration lines.
     * @param array  $lines
     * @return $this
     */
    public function inject($placeholder, $wrapper, array $lines)
    {
        if (!in_array($placeholder, $this->getPlaceholders())) {
            throw new InjectorException("Undefined config placeholder '{$placeholder}'.");
        }

        //Let's prepare lines to be injected
        $lines = $this->prepare($this->placeholderIndentation($placeholder), $wrapper, $lines);

        if ($this->hasLines($lines)) {

            //Already registered
            return $this;
        }

        $offset = $this->placeholderOffset($placeholder);

        //Injecting!
        $this->lines = array_merge(
            array_slice($this->lines, 0, $offset),
            $lines,
            array_slice($this->lines, $offset)
        );

        return $this;
    }

    /**
     * Check rendered file syntax.
     *
     * @return bool
     */
    public function checkSyntax()
    {
        try {
            $tempFilename = tempnam(sys_get_temp_dir(), 'spl');
            file_put_contents($tempFilename, $this->render());

            $process = new Process(PHP_BINARY . " -l {$tempFilename}");

            return $process->run() === 0;
        } finally {
            unlink($tempFilename);
        }
    }

    /**
     * @return string
     */
    public function render()
    {
        return join("\n", $this->lines);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Check if given set of lines already exists in config (including indentation).
     *
     * @param array $lines
     * @return bool
     */
    protected function hasLines(array $lines)
    {
        $offset = 0;
        foreach ($this->lines as $line) {
            if (trim($line) == trim($lines[$offset])) {
                $offset++;

                if ($offset == count($lines)) {
                    //All matched
                    return true;
                }

                continue;
            }

            //Restart search
            $offset = 0;
        }

        return $offset == count($lines);
    }

    /**
     * Indentation string associated with given placeholder.
     *
     * @param string $placeholder
     * @return string
     */
    private function placeholderIndentation($placeholder)
    {
        foreach ($this->lines as $line) {
            if (preg_match(static::PLACEHOLDER_REGEX, $line, $matches)) {
                if ($matches[1] == $placeholder) {
                    return substr($line, 0, strpos($line, $matches[0]));
                }
            }
        }

        throw new InjectorException("Undefined config placeholder '{$placeholder}'.");
    }

    /**
     * Get line where placeholder located.
     *
     * @param string $placeholder
     * @return int
     */
    private function placeholderOffset($placeholder)
    {
        foreach ($this->lines as $number => $line) {
            if (preg_match(static::PLACEHOLDER_REGEX, $line, $matches)) {
                if ($matches[1] == $placeholder) {
                    return $number;
                }
            }
        }

        throw new InjectorException("Undefined config placeholder '{$placeholder}'.");
    }

    /**
     * Prepare set of lines to be injected by adding indentation, missing commas and wrapping lines
     * using given string. See examples in configs.
     *
     * @param string $indent
     * @param string $id
     * @param array  $lines
     * @return array
     */
    private function prepare($indent, $id, array $lines)
    {
        $result = [];
        if (!empty($id)) {
            $id = "/*~[{$id}]:" . date('Y-m-d') . "~*/";
            $result = [$indent . $id];
        }

        foreach ($lines as $line) {
            $result[] = $indent . $line;
        }

        if (!empty($line) && $line[strlen($line) - 1] != ',') {
            //Let's add a comma, by internal contract all config values must locate inside arrays
            $result[count($result) - 1] .= ',';
        }

        if (!empty($id)) {
            $result[] = $indent . $id;
        }

        return $result;
    }
}