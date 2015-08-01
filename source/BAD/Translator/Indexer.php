<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Translator;

use Spiral\Core\Component;
use Spiral\Debug\Traits\LoggerTrait;
use Spiral\Events\Traits\EventsTrait;
use Spiral\Files\FilesInterface;
use Spiral\Proxies\I18n;
use Spiral\Tokenizer\Reflections\FunctionUsage;
use Spiral\Tokenizer\Tokenizer;
use Spiral\Translator\Traits\TranslatorTrait;

class Indexer extends Component
{
    use LoggerTrait, EventsTrait;

    /**
     * List of found function or message usages grouped by bundle id. Every found usage will be
     * automatically registered in i18n component, this array generated only for reference.
     *
     * @var array
     */
    protected $bundles = [];

    /**
     * I18nManager component.
     *
     * @var Translator
     */
    protected $translator = null;

    /**
     * File component.
     *
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * Tokenizer.
     *
     * @var Tokenizer
     */
    protected $tokenizer = null;

    /**
     * New indexer instance.
     *
     * @param Translator     $translator
     * @param Tokenizer      $tokenizer Yes, it requires specific tokenizer implementation.
     * @param FilesInterface $file
     */
    public function __construct(Translator $translator, Tokenizer $tokenizer, FilesInterface $file)
    {
        $this->translator = $translator;
        $this->files = $file;
        $this->tokenizer = $tokenizer;
    }

    /**
     * List of all found bundles, strings and their locations.
     *
     * @return array
     */
    public function getBundles()
    {
        return $this->bundles;
    }

    /**
     * Parse all files in specified directory to find i18n functions and register them with i18n
     * component. Supported functions: l, p, Translator::translate (proxy),
     * Translator::pluralize (proxy).
     *
     * Both bundle id and message should be string constants, otherwise usage will not be recorded.
     *
     * @param string $directory Directory which has to be indexed. Application directory by default.
     * @param array  $excludes  Skip indexation if keyword met in filename.
     * @return Indexer
     */
    public function indexDirectory($directory = null, array $excludes = [])
    {
        foreach ($this->files->getFiles($directory, 'php') as $filename)
        {
            $filename = $this->files->normalizePath($filename);
            foreach ($excludes as $exclude)
            {
                if (strpos($filename, $exclude) !== false)
                {
                    continue 2;
                }
            }

            $fileReflection = $this->tokenizer->reflectionFile($filename);
            $this->indexUsages($fileReflection->getFunctionUsages());
        }

        return $this;
    }

    /**
     * Register all translator functions in appropriate bundles.
     *
     * @param FunctionUsage[] $usages
     * @throws IndexerException
     */
    protected function indexUsages(array $usages)
    {
        foreach ($usages as $usage)
        {
            $firstArgument = $usage->getArgument(0);
            if (empty($firstArgument) || $firstArgument->getType() != FunctionUsage\Argument::STRING)
            {
                //Every translation function require first argument to be a string, not expression
                continue;
            }

            if (!empty($usage->getClass()) && $usage->getClass() != I18n::class)
            {
                if ($usage->getFunction() == 'translate')
                {
                    //Can be part of TranslatorTrait
                    $this->indexTraitUsage($usage);
                }

                //We are looking for one specific class
                continue;
            }

            if ($usage->getFunction() == 'p' || $usage->getFunction() == 'pluralize')
            {
                $this->translator->pluralize($firstArgument->stringValue(), 0);

                //Registering plural usage
                $this->registerString(
                    $usage->getFilename(),
                    $usage->getLine(),
                    $this->translator->getConfig()['plurals'],
                    $firstArgument->stringValue()
                );
            }

            if ($usage->getFunction() == 'l')
            {
                $this->translator->translate(Translator::DEFAULT_BUNDLE, $firstArgument->stringValue());

                //Translate using default bundle
                $this->registerString(
                    $usage->getFilename(),
                    $usage->getLine(),
                    Translator::DEFAULT_BUNDLE,
                    $firstArgument->stringValue()
                );
            }

            if ($usage->getFunction() == 'translate')
            {
                $secondArgument = $usage->getArgument(1);
                if (empty($secondArgument) || $secondArgument->getType() != FunctionUsage\Argument::STRING)
                {
                    //We can only use static strings
                    continue;
                }

                //Registering string
                $this->translator->translate(
                    $firstArgument->stringValue(),
                    $secondArgument->stringValue()
                );

                //Translate with specified bundle
                $this->registerString(
                    $usage->getFilename(),
                    $usage->getLine(),
                    $firstArgument->stringValue(),
                    $secondArgument->stringValue()
                );
            }
        }
    }

    /**
     * Perform indexation of translate() methods. System will check if this method belongs to
     * TranslatorTrait.
     *
     * @param FunctionUsage $usage
     */
    protected function indexTraitUsage(FunctionUsage $usage)
    {
        if (!in_array(TranslatorTrait::class, $this->tokenizer->getTraits($usage->getClass())))
        {
            return;
        }

        $string = $usage->getArgument(0)->stringValue();

        if (
            substr($string, 0, 2) == Translator::I18N_PREFIX
            || substr($string, -2) == Translator::I18N_POSTFIX
        )
        {
            //This string was defined in class attributes
            $string = substr($string, 2, -2);
        }

        $this->translator->translate($usage->getClass(), $string);

        $this->registerString(
            $usage->getFilename(),
            $usage->getLine(),
            $usage->getClass(),
            $string,
            $usage->getClass()
        );
    }

    /**
     * Will index localization string defined in default values of classes with TranslatorTrait.
     * Strings should have [[ ]].
     *
     * @param string $namespace Namespace to collect models from, core namespace by default.
     * @return Indexer
     */
    public function indexClasses($namespace = '')
    {
        $classes = $this->tokenizer->getClasses(TranslatorTrait::class, $namespace);
        foreach ($classes as $class => $location)
        {
            $reflection = new \ReflectionClass($class);

            //We have to merge both local and parent class messages
            $recursively = $reflection->getConstant('INHERIT_TRANSLATIONS');

            foreach ($this->fetchStrings($reflection, $recursively) as $string)
            {
                $this->translator->translate($reflection->getName(), $string);
                $this->registerString(
                    $reflection->getFileName(),
                    $reflection->getStartLine(),
                    $reflection->getName(),
                    $string,
                    $reflection->getName()
                );
            }
        }
    }

    /**
     * Fetching strings has to be localized from class default values, values can be fetched recursively
     * and merged with parent data.
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

        $strings = [];
        array_walk_recursive($defaultProperties, function ($value) use (&$strings)
        {
            if (
                is_string($value)
                && substr($value, 0, 2) == Translator::I18N_PREFIX
                && substr($value, -2) == Translator::I18N_POSTFIX
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
     * Register new string with specified location and bundle.
     *
     * @param string $filename File where string were found.
     * @param string $line     Line string begins on.
     * @param string $bundle   I18n bundle id.
     * @param string $string   Used string value.
     * @param string $class    Class were strings were found.
     */
    protected function registerString($filename, $line, $bundle, $string, $class = '')
    {
        $payload = compact('filename', 'line', 'bundle', 'string', 'class');

        if ($class)
        {
            $this->logger()->info("'{string}' found in class '{class}'.", $payload);
        }
        else
        {
            $this->logger()->info(
                "'{string}' found in bundle '{bundle}' used in '{filename}' at line {line}.", $payload
            );
        }

        $this->bundles[$bundle][] = $this->fire('string', $payload);
    }
}