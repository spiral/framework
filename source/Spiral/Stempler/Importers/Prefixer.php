<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Stempler\Importers;

use Spiral\Stempler\ImporterInterface;
use Spiral\Views\Exceptions\ViewsException;
use Spiral\Views\Processors\TemplateProcessor;

/**
 * Namespace importer provides ability to include multiple elements using common namespace prefix.
 *
 * Example: namespace:folder/* => namespace:folder/name
 */
class Prefixer implements ImporterInterface
{
    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var string
     */
    private $target = '';

    /**
     * @param string $prefix
     * @param string $target
     */
    public function __construct($prefix, $target)
    {
        $this->prefix = $prefix;
        $this->target = $target;
    }

    /**
     * {@inheritdoc}
     */
    public function importable($element, array $token)
    {
        $element = strtolower($element);

        return strpos($element, $this->prefix) === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function resolvePath($element, array $token)
    {
        $element = substr($element, strlen($this->prefix));

        return str_replace('*', str_replace('.', '/', $element), $this->target);
    }
}