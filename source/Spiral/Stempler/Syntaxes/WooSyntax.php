<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Stempler\Syntaxes;

use Spiral\Stempler\Exceptions\SyntaxException;
use Spiral\Stempler\Exporters\AttributesExporter;
use Spiral\Stempler\HtmlTokenizer;
use Spiral\Stempler\Importers\Aliaser;
use Spiral\Stempler\Importers\Bundler;
use Spiral\Stempler\Importers\Prefixer;
use Spiral\Stempler\Importers\Stopper;
use Spiral\Stempler\Supervisor;
use Spiral\Stempler\SyntaxInterface;

/**
 * Default Stempler syntax - Woo. Provides ability to define blocks, extends and includes.
 */
class WooSyntax implements SyntaxInterface
{
    /**
     * Path attribute in extends and other nodes.
     */
    const PATH_ATTRIBUTE = 'path';

    /**
     * @var bool
     */
    private $strict = true;

    /**
     * Stempler syntax options, syntax and names. Every option is required.
     *
     * @todo Something with DTD? Generally feels compatible, maybe no widgets.
     * @var array
     */
    protected $constructions = [
        self::TYPE_BLOCK    => ['block:', 'section:', 'yield:', 'define:'],
        self::TYPE_EXTENDS  => [
            'extends:',
            'extends',
            'woo:extends',
            'layout:extends'
        ],
        self::TYPE_IMPORTER => ['woo:use', 'use', 'node:use', 'stempler:use']
    ];

    /**
     * @param bool $strict
     */
    public function __construct($strict = true)
    {
        $this->strict = $strict;
    }

    /**
     * {@inheritdoc}
     */
    public function tokenType(array $token, &$name = null)
    {
        $name = $token[HtmlTokenizer::TOKEN_NAME];
        foreach ($this->constructions as $type => $prefixes) {
            foreach ($prefixes as $prefix) {
                if (strpos($name, $prefix) === 0) {
                    //We found prefix pointing to needed behaviour
                    $name = substr($name, strlen($prefix));

                    return $type;
                }
            }
        }

        return self::TYPE_NONE;
    }

    /**
     * {@inheritdoc}
     */
    public function resolvePath(array $token)
    {
        $type = $this->tokenType($token, $name);

        if (isset($token[HtmlTokenizer::TOKEN_ATTRIBUTES][static::PATH_ATTRIBUTE])) {
            return $token[HtmlTokenizer::TOKEN_ATTRIBUTES][static::PATH_ATTRIBUTE];
        }

        if ($type == self::TYPE_EXTENDS && isset($token[HtmlTokenizer::TOKEN_ATTRIBUTES]['layout'])) {
            return $token[HtmlTokenizer::TOKEN_ATTRIBUTES]['layout'];
        }

        return $name;
    }

    /**
     * {@inheritdoc}
     */
    public function isStrict()
    {
        return $this->strict;
    }

    /**
     * {@inheritdoc}
     */
    public function createImporter(array $token, Supervisor $supervisor)
    {
        //Fetching path
        $path = $this->resolvePath($token);
        if (empty($attributes = $token[HtmlTokenizer::TOKEN_ATTRIBUTES])) {
            throw new SyntaxException("Invalid import element syntax, attributes missing.", $token);
        }

        /**
         * <woo:use bundle="path-to-bundle"/>
         */
        if (isset($attributes['bundle'])) {
            $path = $attributes['bundle'];

            return new Bundler($supervisor, $path, $token);
        }

        /**
         * <woo:use path="path-to-element" as="tag"/>
         * <woo:use path="path-to-element" element="tag"/>
         */
        if (isset($attributes['element']) || isset($attributes['as'])) {
            $alias = isset($attributes['element']) ? $attributes['element'] : $attributes['as'];

            return new Aliaser($alias, $path);
        }

        //Now we have to decide what importer to use
        if (isset($attributes['namespace']) || isset($attributes['prefix'])) {
            if (strpos($path, '*') === false) {
                throw new SyntaxException(
                    "Path in namespace/prefix import must include start symbol.", $token
                );
            }

            $prefix = isset($attributes['namespace'])
                ? $attributes['namespace'] . ':'
                : $attributes['prefix'];

            return new Prefixer($prefix, $path);
        }

        if (isset($attributes['stop'])) {
            return new Stopper($attributes['stop']);
        }

        throw new SyntaxException("Undefined use element.", $token);
    }

    /**
     * {@inheritdoc}
     */
    public function blockExporters()
    {
        return [
            new AttributesExporter()
        ];
    }
}