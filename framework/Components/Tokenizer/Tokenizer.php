<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Tokenizer;

use Spiral\Components\Files\FileManager;
use Spiral\Core\Component;
use Spiral\Core\Core;
use ReflectionClass;
use Spiral\Core\CoreInterface;
use Spiral\Core\Events\Event;
use Spiral\Core\Loader;

class Tokenizer extends Component
{
    /**
     * Will provide us helper method getInstance().
     */
    use Component\SingletonTrait, Component\ConfigurableTrait, Component\LoggerTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = 'tokenizer';

    /**
     * Token array constants.
     */
    const TYPE = 0;
    const CODE = 1;
    const LINE = 2;

    /**
     * Core to check bindings and store classes cache.
     *
     * @invisible
     * @var CoreInterface
     */
    protected $core = null;

    /**
     * FileManager component to load files.
     *
     * @invisible
     * @var FileManager
     */
    protected $file = null;

    /**
     * Loader component instance.
     *
     * @invisible
     * @var Loader
     */
    protected $loader = null;

    /**
     * Rules and styles to highlight code using tokens. This rules used in Tokenizer->getCode()
     * method to colorize some php parts in exceptions. Rule specified by: "style" => array(tokens),
     * example:
     * Tokenizer->setHighlighting(array(
     *      'color: blue' => array(
     *          T_DNUMBER, T_LNUMBER
     *      )
     * ));
     *
     * @var array
     */
    protected $highlighting = array();

    /**
     * Cache of already processed file reflections, used to speed up lookup.
     *
     * @var array
     */
    protected $cache = array();

    /**
     * Tokenizer used by spiral to fetch list of available classes, their declarations and locations.
     * This class mostly used for indexing, orm and odm schemas and etc. Additionally this class has
     * ability to perform simple PHP code highlighting which can be used in ExceptionResponses and
     * snapshots.
     *
     * @param CoreInterface $core
     * @param FileManager   $file
     * @param Loader        $loader
     */
    public function __construct(CoreInterface $core, FileManager $file, Loader $loader)
    {
        $this->core = $core;
        $this->file = $file;
        $this->loader = $loader;

        $this->config = $core->loadConfig('tokenizer');

        foreach ($this->config['directories'] as &$directory)
        {
            $directory = $file->normalizePath($directory, true);
            unset($directory);
        }
    }

    /**
     * Rules and styles to highlight code using tokens. This rules used in Tokenizer->getCode() method
     * to colorize some php parts in exceptions. Rule specified by: "style" => array(tokens), example:
     *
     * Tokenizer->setHighlighting(array(
     *      'color: blue' => array(
     *          T_DNUMBER, T_LNUMBER
     *      )
     * ));
     *
     * @param array $highlighting
     * @return static
     */
    public function setHighlightingStyles($highlighting)
    {
        $this->highlighting = $highlighting;

        return $this;
    }

    /**
     * Fetch specified amount of lines from provided filename and highlight them according to specified
     * highlighting rules (setHighlighting() method), target (middle) line number are specified in
     * "$targetLine" argument and will be used as reference to count lines before and after.
     *
     * Example:
     * line = 10, countLines = 10
     *
     * Output:
     * lines from 5 - 15 will be displayed, line 10 will be highlighted.
     *
     * @param string $filename   Filename to fetch and highlight lines from.
     * @param int    $targetLine Line number where code should be highlighted from.
     * @param int    $countLines Lines to fetch before and after code line specified in previous
     *                           argument.
     * @return string
     */
    public function highlightCode($filename, $targetLine, $countLines = 10)
    {
        $tokens = $this->fetchTokens($filename);

        $phpLines = "";
        foreach ($tokens as $position => $token)
        {
            $token[self::CODE] = htmlentities($token[self::CODE]);

            foreach ($this->highlighting as $style => $tokens)
            {
                //This way is slower, but more tolerant to memory usage
                if (in_array($token[self::TYPE], $tokens))
                {
                    if (strpos($token[self::CODE], "\n"))
                    {
                        $lines = array();
                        foreach (explode("\n", $token[self::CODE]) as $line)
                        {
                            $lines[] = '<span style="' . $style . '">'
                                . $line
                                . '</span>';
                        }

                        $token[self::CODE] = join("\n", $lines);
                    }
                    else
                    {
                        $token[self::CODE] = '<span style="' . $style . '">'
                            . $token[self::CODE]
                            . '</span>';
                    }
                    break;
                }
            }

            $phpLines .= $token[self::CODE];
        }

        $phpLines = explode("\n", str_replace("\r\n", "\n", $phpLines));
        $result = "";
        foreach ($phpLines as $line => $code)
        {
            $line++;
            if ($line >= $targetLine - $countLines && $line <= $targetLine + $countLines)
            {
                $result .= "<div class=\"" . ($line == $targetLine ? "highlighted" : "") . "\">"
                    . "<div class=\"number\">{$line}</div>"
                    . mb_convert_encoding($code, 'utf-8')
                    . "</div>";
            }
        }

        return $result;
    }

    /**
     * Fetch tokens from specified filename. String tokens will be automatically extended with their
     * type and line.
     *
     * @param string $filename
     * @return array
     */
    public function fetchTokens($filename)
    {
        $tokens = token_get_all($this->file->read($filename));

        $line = 0;
        foreach ($tokens as &$token)
        {
            if (isset($token[self::LINE]))
            {
                $line = $token[self::LINE];
            }

            if (!is_array($token))
            {
                $token = array($token, $token, $line);
            }

            unset($token);
        }

        return $tokens;
    }

    /**
     * Index all available files excluding excludeDirectories and generate list of found classes with
     * their names and filenames. Unreachable classes or files with conflicts be skipped and debug
     * messages will generated.
     *
     * This is SLOW method, should be used only for static analysis.
     *
     * @param mixed  $parent    Class or interface should be extended. By default - null (all classes).
     *                          Parent will also be included to classes list as one of results.
     * @param string $namespace Only classes in this namespace will be retrieved, null by default
     *                          (all namespaces).
     * @param string $postfix   Only classes with such postfix will be analyzed, empty by default.
     * @param bool   $debug     If enabled additional debug messages will be raised.
     * @return array
     */
    public function getClasses($parent = null, $namespace = null, $postfix = '', $debug = false)
    {
        $result = array();
        $namespace = ltrim($namespace, '\\');

        if (!empty($parent) && (is_object($parent) || is_string($parent)))
        {
            $parent = new ReflectionClass($parent);
        }

        $this->loader->enable();
        $this->loader->dispatcher()->addListener('notFound', $loaderException = function (Event $event)
        {
            throw new TokenizerException("Class {$event->context['class']} can not be loaded.");
        });

        //Disabling caching during lookup
        foreach ($this->config['directories'] as $directory)
        {
            foreach ($this->file->getFiles($directory, array('php')) as $filename)
            {
                $filename = $this->file->normalizePath($filename);
                foreach ($this->config['exclude'] as $exclude)
                {
                    if (strpos($filename, $exclude) !== false)
                    {
                        continue 2;
                    }
                }

                $reflectionFile = $this->fileReflection($filename);

                if ($reflectionFile->hasIncludes())
                {
                    self::logger()->warning(
                        "File '{filename}' has includes and will be excluded from analysis.",
                        array(
                            'filename' => $this->file->relativePath($filename)
                        )
                    );

                    continue;
                }

                //We need only classes
                foreach ($reflectionFile->getClasses() as $class)
                {
                    if (!empty($namespace) && strpos(ltrim($class, '\\'), $namespace) === false)
                    {
                        continue;
                    }

                    if (!empty($postfix) && substr($class, -1 * strlen($postfix)) != $postfix)
                    {
                        continue;
                    }

                    try
                    {
                        $reflection = new ReflectionClass($class);

                        if (!empty($parent))
                        {
                            if ($parent->isTrait())
                            {
                                if (!in_array($parent->getName(), self::getTraits($class)))
                                {
                                    continue;
                                }
                            }
                            else
                            {
                                if (
                                    !$reflection->isSubclassOf($parent)
                                    && $reflection->getName() != $parent->getName()
                                )
                                {
                                    continue;
                                }
                            }
                        }

                        $result[$class] = array(
                            'name'     => $reflection->getName(),
                            'filename' => $filename,
                            'abstract' => $reflection->isAbstract()
                        );

                        if ($debug)
                        {
                            self::logger()->info(
                                "Class '{class}' has been successfully analyzed.",
                                compact('class')
                            );
                        }
                    }
                    catch (\Exception $exception)
                    {
                        self::logger()->error(
                            "Unable to resolve class '{class}', error \"{message}\".",
                            array(
                                'filename' => $this->file->relativePath($filename),
                                'class'    => $class,
                                'message'  => $exception->getMessage()
                            )
                        );
                    }
                }
            }
        }

        $this->loader->dispatcher()->removeListener('notFound', $loaderException);

        return $result;
    }


    /**
     * Get all class traits.
     *
     * @param string $class
     * @return array
     */
    public static function getTraits($class)
    {
        $traits = [];

        while ($class)
        {
            $traits = array_merge(class_uses($class), $traits);
            $class = get_parent_class($class);
        }

        //Traits from traits
        foreach (array_flip($traits) as $trait)
        {
            $traits = array_merge(class_uses($trait), $traits);
        }

        return array_unique($traits);
    }

    /**
     * Check if file cache is presented.
     *
     * @param string $filename
     * @return bool
     */
    protected function hasFileCache($filename)
    {
        return isset($this->cache[$filename])
        && $this->cache[$filename]['md5'] == $this->file->md5($filename);
    }

    /**
     * Get ReflectionFile for given filename, reflection can be used to retrieve list of declared
     * classes, interfaces, traits and functions, additional it can locate function usages.
     *
     * @param string $filename PHP filename.
     * @return ReflectionFile
     */
    public function fileReflection($filename)
    {
        if (empty($this->cache))
        {
            $this->cache = $this->core->loadData('tokenizer-reflections');
        }

        if ($this->hasFileCache($filename))
        {
            $reflectionFile = new ReflectionFile($filename, $this, $this->cache[$filename]);
        }
        else
        {
            $reflectionFile = new ReflectionFile($filename, $this);

            $this->cache[$filename] = array(
                    'md5' => $this->file->md5($filename)
                ) + $reflectionFile->exportSchema();

            $this->core->saveData('tokenizer-reflections', $this->cache);
        }

        return $reflectionFile;
    }
}