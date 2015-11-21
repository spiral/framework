<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Stempler\Importers;

use Spiral\Stempler\ImporterInterface;
use Spiral\Stempler\Stempler;

/**
 * Declares to templater that element must be treated as html tag, not Node include. Stop keyword
 * must be located in
 * "stop" attribute of tag caused import.
 */
class Stopper implements ImporterInterface
{
    /**
     * Html tag name.
     *
     * @var string
     */
    protected $element = '';

    /**
     * @param string $element
     */
    public function __construct($element)
    {
        $this->element = $element;
    }

    /**
     * {@inheritdoc}
     */
    public function importable($element, array $token)
    {
        if ($this->element == '*') {
            //To disable every lower level importer, you can still define more importers after that
            return true;
        }

        return strtolower($element) == strtolower($this->element);
    }

    /**
     * {@inheritdoc}
     */
    public function resolvePath($element, array $token)
    {
        return null;
    }
}