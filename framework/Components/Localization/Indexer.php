<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Localization;

use Spiral\Components\Files\FileManager;
use Spiral\Components\Tokenizer\Reflection\FunctionUsage;
use Spiral\Components\Tokenizer\Reflection\FunctionUsage\Argument;
use Spiral\Components\Tokenizer\Tokenizer;
use Spiral\Core\Component;

class Indexer extends Component
{
    /**
     * Logging found usages.
     */
    use Component\LoggerTrait, Component\EventsTrait;

    /**
     * Trait declares I18n messages support.
     */
    const LOCALIZABLE_TRAIT = 'Spiral\Components\Localization\LocalizableTrait';

    /**
     * List of found function or message usages grouped by bundle id. Every found usage will be
     * automatically registered in i18n component, this array generated only for reference.
     *
     * @var array
     */
    protected $foundUsages = array();

    /**
     * I18nManager component.
     *
     * @var I18nManager
     */
    protected $i18n = null;

    /**
     * File component.
     *
     * @var FileManager
     */
    protected $file = null;

    /**
     * Tokenizer.
     *
     * @var Tokenizer
     */
    protected $tokenizer = null;

    /**
     * New indexer instance.
     *
     * @param I18nManager $i18n
     * @param FileManager $file
     * @param Tokenizer   $tokenizer
     */
    public function __construct(I18nManager $i18n, FileManager $file, Tokenizer $tokenizer)
    {
        $this->i18n = $i18n;
        $this->file = $file;
        $this->tokenizer = $tokenizer;
    }

    /**
     * Parse all files in specified directory to find i18n functions and register them with i18n
     * component. Supported functions: l, p, I18n::get, I18n::pluralize
     *
     * Both bundle id and message should be string constants, otherwise usage will not be recorded.
     * Do not use i18n methods via non-static call.
     *
     * @param string $directory Directory which has to be indexed. Application directory by default.
     * @return Indexer
     */
    public function indexDirectory($directory = null)
    {
        $directory = $directory ?: directory('application');

        foreach ($this->file->getFiles($directory, 'php') as $filename)
        {
            $fileReflection = $this->tokenizer->fileReflection($filename);
            $this->registerFunctions($filename, $fileReflection->functionUsages());
        }

        return $this;
    }

    /**
     * Register all i18n functions in appropriate bundles.
     *
     * @param FunctionUsage[] $functions
     * @param string          $filename
     * @throws IndexerException
     */
    protected function registerFunctions($filename, array $functions)
    {
        foreach ($functions as $function)
        {
            if (!$function->getClass())
            {
                if ($function->getFunction() == 'l')
                {
                    if (!$function->getArgument(0))
                    {
                        //Some arguments missing
                        continue;
                    }

                    if ($function->getArgument(0)->getType() != Argument::STRING)
                    {
                        continue;
                    }

                    $this->i18n->get(I18nManager::DEFAULT_BUNDLE, $function->getArgument(0)->stringValue());
                    $this->registerString(
                        $filename,
                        $function->getLine(),
                        I18nManager::DEFAULT_BUNDLE,
                        $function->getArgument(0)->stringValue());
                }

                if ($function->getFunction() == 'p')
                {
                    if (!$function->getArgument(0))
                    {
                        //Some arguments missing
                        continue;
                    }

                    if ($function->getArgument(0)->getType() != Argument::STRING)
                    {
                        continue;
                    }

                    $this->i18n->pluralize($function->getArgument(0)->stringValue(), 0);
                    $this->registerString(
                        $filename,
                        $function->getLine(),
                        $this->i18n->getConfig()['plurals'],
                        $function->getArgument(0)->stringValue()
                    );
                }
            }

            if ($function->getClass() == 'Spiral\Facades\I18n')
            {
                if ($function->getFunction() == 'get')
                {
                    if (!$function->getArgument(0) || !$function->getArgument(1))
                    {
                        //Some arguments missing
                        continue;
                    }

                    if ($function->getArgument(0)->getType() != Argument::STRING)
                    {
                        continue;
                    }

                    if ($function->getArgument(1)->getType() != Argument::STRING)
                    {
                        continue;
                    }

                    //Registering string
                    $this->i18n->get(
                        $function->getArgument(0)->stringValue(),
                        $function->getArgument(1)->stringValue()
                    );

                    $this->registerString(
                        $filename,
                        $function->getLine(),
                        $function->getArgument(0)->stringValue(),
                        $function->getArgument(1)->stringValue()
                    );
                }

                if ($function->getFunction() == 'pluralize')
                {
                    if (!$function->getArgument(0))
                    {
                        //Some arguments missing
                        continue;
                    }

                    if ($function->getArgument(0)->getType() != Argument::STRING)
                    {
                        continue;
                    }

                    $this->i18n->pluralize($function->getArgument(0)->stringValue(), 0);
                    $this->registerString(
                        $filename,
                        $function->getLine(),
                        $this->i18n->getConfig()['plurals'],
                        $function->getArgument(0)->stringValue()
                    );
                }
            }

            if ($function->getFunction() == 'i18nMessage')
            {
                $this->indexMessageFunction($filename, $function);
            }
        }
    }

    /**
     * Will index localization string defined in default values of classes with LocalizableTrait trait.
     * Strings should have [[ ]]. Method will additionally find all i18nMessage method usages. Only
     * statically used methods will be indexed!
     *
     * @param string $namespace Namespace to collect models from, core namespace by default.
     * @return Indexer
     */
    public function indexClasses($namespace = '')
    {
        foreach ($this->tokenizer->getClasses(self::LOCALIZABLE_TRAIT, $namespace) as $class =>
                 $location)
        {
            //Indexing class
            $reflection = new \ReflectionClass($class);

            //Class strings
            $strings = $this->fetchStrings($reflection);

            //Parent class strings
            $parentStrings = $reflection->getParentClass()
                ? $this->fetchStrings($reflection->getParentClass(), true)
                : array();

            //Only unique
            if ($strings = array_diff($strings, $parentStrings))
            {
                $bundle = call_user_func(array($reflection->getName(), 'i18nBundle'));

                foreach ($strings as $string)
                {
                    $this->i18n->get($bundle, $string);
                    $this->registerString(
                        $location['filename'],
                        0,
                        $bundle,
                        $string,
                        $reflection->getName()
                    );
                }
            }
        }
    }

    /**
     * Perform indexation of i18nMessage() method usage. We have to analyze parent class and fetch
     * it's i18n bundle.
     *
     * @param string        $filename
     * @param FunctionUsage $function
     */
    protected function indexMessageFunction($filename, FunctionUsage $function)
    {

        if (!in_array(self::LOCALIZABLE_TRAIT, Tokenizer::getTraits($function->getClass())))
        {
            return;
        }

        if (!$function->getArgument(0))
        {
            return;
        }

        if ($function->getArgument(0)->getType() != Argument::STRING)
        {
            return;
        }

        $string = $function->getArgument(0)->stringValue();
        if (
            substr($string, 0, 2) == I18nManager::I18N_PREFIX
            || substr($string, -2) == I18nManager::I18N_POSTFIX
        )
        {
            //This string was defined in class attributes
            $string = substr($string, 2, -2);
        }

        $bundle = call_user_func(array($function->getClass(), 'i18nBundle'));

        $this->i18n->get($bundle, $string);
        $this->registerString(
            $filename,
            $function->getLine(),
            $bundle,
            $string,
            $function->getClass()
        );
    }

    /**
     * Fetching strings has to be localized from class default values, values can be fetched recursively
     * and merged
     * with parent data.
     *
     * @param \ReflectionClass $reflection
     * @param bool             $recursively
     * @return array
     */
    protected function fetchStrings(\ReflectionClass $reflection, $recursively = false)
    {
        $defaultProperties = $reflection->getDefaultProperties();

        foreach ($reflection->getProperties() as $property)
        {
            if (strpos($property->getDocComment(), "@do-not-index"))
            {
                unset($defaultProperties[$property->getName()]);
            }
        }

        $strings = array();
        array_walk_recursive($defaultProperties, function ($value) use (&$strings)
        {
            if (
                is_string($value)
                && substr($value, 0, 2) == I18nManager::I18N_PREFIX
                && substr($value, -2) == I18nManager::I18N_POSTFIX
            )
            {
                $strings[] = substr($value, 2, -2);
            }
        });

        if ($recursively && $reflection->getParentClass())
        {
            $strings = array_merge($strings, $this->fetchStrings($reflection->getParentClass(), true));
        }

        return $strings;
    }

    /**
     * Registering new string with specified location and bundle.
     *
     * @param string $filename File where string were found.
     * @param string $line     Line string begins on.
     * @param string $bundle   I18n bundle id.
     * @param string $string   Used string value.
     * @param string $class    Class were strings were found.
     */
    protected function registerString($filename, $line, $bundle, $string, $class = '')
    {
        if ($class)
        {
            $this->logger()->info(
                "'{string}' found in class '{class}'.",
                array(
                    'filename' => $this->file->relativePath($filename),
                    'line'     => $line,
                    'bundle'   => $bundle,
                    'string'   => $string,
                    'class'    => $class
                )
            );
        }
        else
        {
            $this->logger()->info(
                "'{string}' found in bundle '{bundle}' used in '{filename}' at line {line}.",
                array(
                    'filename' => $this->file->relativePath($filename),
                    'line'     => $line,
                    'bundle'   => $bundle,
                    'string'   => $string
                )
            );
        }

        $this->foundUsages[$bundle][] = $this->event('string', compact(
            'filename',
            'line',
            'bundle',
            'string',
            'class'
        ));
    }

    /**
     * List of all found bundles, strings and their locations.
     *
     * @return array
     */
    public function foundStrings()
    {
        return $this->foundUsages;
    }
}