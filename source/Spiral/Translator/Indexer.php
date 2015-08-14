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
use Spiral\Tokenizer\Exceptions\ReflectionException;
use Spiral\Tokenizer\Exceptions\TokenizerException;
use Spiral\Tokenizer\Reflections\ReflectionArgument;
use Spiral\Tokenizer\Reflections\ReflectionCall;
use Spiral\Tokenizer\Tokenizer;
use Spiral\Translator\Exceptions\IndexerException;
use Spiral\Translator\Exceptions\TranslatorException;
use Spiral\Translator\Traits\TranslatorTrait;

/**
 * Index available classes and function calls to fetch every used string translation. Can understand
 * l, p and translate (trait) function. Only static calls will be indexes.
 *
 * In addition indexes will find every string specified in default value of model or class which
 * uses TranslatorTrait. String has to be embraced with [[ ]] in order to be indexed, you can disable
 * property indexation using @do-not-index doc comment. Translator can merge strings with parent data,
 * set class constant INHERIT_TRANSLATIONS to true.
 */
class Indexer extends Component
{
    /**
     * Provides event "string" when new string is found.
     */
    use LoggerTrait, EventsTrait;

    /**
     * Every indexed string grouped by parent bundle.
     *
     * @var array
     */
    private $bundles = [];

    /**
     * @var Translator
     */
    protected $translator = null;

    /**
     * @var Tokenizer
     */
    protected $tokenizer = null;

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @param Translator     $translator
     * @param Tokenizer      $tokenizer Indexer required specific Tokenizer implementation.
     * @param FilesInterface $files
     */
    public function __construct(Translator $translator, Tokenizer $tokenizer, FilesInterface $files)
    {
        $this->translator = $translator;
        $this->tokenizer = $tokenizer;
        $this->files = $files;
    }

    /**
     * Index directory files to locate function calls related to Translator or TranslatorTrait
     * methods.
     *
     * @param string $directory
     * @param array  $excludes Filename words to be excluded from analysis.
     * @return self
     * @throws TokenizerException
     * @throws ReflectionException
     * @throws TranslatorException
     */
    public function indexDirectory($directory = null, array $excludes = [])
    {
        foreach ($this->files->getFiles($directory, 'php') as $filename) {
            foreach ($excludes as $exclude) {
                if (strpos($filename, $exclude) !== false) {
                    continue 2;
                }
            }

            $this->indexCalls(
                $this->tokenizer->fileReflection($filename)->getCalls()
            );
        }

        return $this;
    }

    /**
     * Index default strings embraced with [[ ]] and belongs to classes used TranslatorTrait.
     *
     * @param string $namespace
     * @return self
     * @throws TokenizerException
     * @throws ReflectionException
     * @throws TranslatorException
     */
    public function indexClasses($namespace = '')
    {
        $classes = $this->tokenizer->getClasses(TranslatorTrait::class, $namespace);
        foreach ($classes as $class => $location) {
            $reflection = new \ReflectionClass($class);

            //We have to merge both local and parent class messages
            $recursively = $reflection->getConstant('INHERIT_TRANSLATIONS');

            foreach ($this->fetchStrings($reflection, $recursively) as $string) {
                $this->translator->translate($reflection->getName(), $string);

                $this->register(
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
     * Total bundles were found.
     *
     * @return array
     */
    public function countBundles()
    {
        return count($this->bundles);
    }

    /**
     * Total string were found.
     *
     * @return array
     */
    public function countStrings()
    {
        $result = 0;
        foreach ($this->bundles as $bundle) {
            $result += count($bundle);
        }

        return $result;
    }

    /**
     * Index translator relation function calls.
     *
     * @param ReflectionCall[] $calls
     * @throws IndexerException
     */
    private function indexCalls(array $calls)
    {
        foreach ($calls as $call) {
            $firstArgument = $call->argument(0);
            if (empty($firstArgument) || $firstArgument->getType() != ReflectionArgument::STRING) {
                //Every translation function require first argument to be a string, not expression
                continue;
            }

            if (!empty($call->getClass()) && $call->getClass() != I18n::class) {
                if ($call->getName() == 'translate') {
                    //Can be part of TranslatorTrait
                    $this->indexTrait($call);
                }

                //We are looking for one specific class
                continue;
            }

            if ($call->getName() == 'p' || $call->getName() == 'pluralize') {
                $this->translator->pluralize($firstArgument->stringValue(), 0);

                //Registering plural usage
                $this->register(
                    $call->getFilename(),
                    $call->getLine(),
                    $this->translator->config()['plurals'],
                    $firstArgument->stringValue()
                );
            }

            if ($call->getName() == 'l') {
                $this->translator->translate(Translator::DEFAULT_BUNDLE,
                    $firstArgument->stringValue());

                //Translate using default bundle
                $this->register(
                    $call->getFilename(),
                    $call->getLine(),
                    Translator::DEFAULT_BUNDLE,
                    $firstArgument->stringValue()
                );
            }

            if ($call->getName() == 'translate') {
                $secondArgument = $call->argument(1);
                if (empty($secondArgument) || $secondArgument->getType() != ReflectionArgument::STRING) {
                    //We can only use static strings
                    continue;
                }

                //Registering string
                $this->translator->translate(
                    $firstArgument->stringValue(),
                    $secondArgument->stringValue()
                );

                //Translate with specified bundle
                $this->register(
                    $call->getFilename(),
                    $call->getLine(),
                    $firstArgument->stringValue(),
                    $secondArgument->stringValue()
                );
            }
        }
    }

    /**
     * Handle usage of translator method belongs to TranslatorTrait.
     *
     * @param ReflectionCall $call
     */
    protected function indexTrait(ReflectionCall $call)
    {
        if (!in_array(TranslatorTrait::class, $this->tokenizer->getTraits($call->getClass()))) {
            return;
        }

        $string = $call->argument(0)->stringValue();

        if (
            substr($string, 0, 2) == Translator::I18N_PREFIX
            || substr($string, -2) == Translator::I18N_POSTFIX
        ) {
            //This string was defined in class attributes
            $string = substr($string, 2, -2);
        }

        $this->translator->translate($call->getClass(), $string);

        $this->register(
            $call->getFilename(),
            $call->getLine(),
            $call->getClass(),
            $string,
            $call->getClass()
        );
    }

    /**
     * Fetch default string values from class and merge it with parent strings if requested.
     *
     * @param \ReflectionClass $reflection
     * @param bool             $recursively
     * @return array
     */
    private function fetchStrings(\ReflectionClass $reflection, $recursively = false)
    {
        $defaultProperties = $reflection->getDefaultProperties();
        foreach ($reflection->getProperties() as $property) {
            if (strpos($property->getDocComment(), "@do-not-index")) {
                unset($defaultProperties[$property->getName()]);
            }
        }

        $strings = [];
        array_walk_recursive($defaultProperties, function ($value) use (&$strings) {
            if (
                is_string($value)
                && substr($value, 0, 2) == Translator::I18N_PREFIX
                && substr($value, -2) == Translator::I18N_POSTFIX
            ) {
                $strings[] = substr($value, 2, -2);
            }
        });

        if ($recursively && $reflection->getParentClass()) {
            $strings = array_merge($strings, $this->fetchStrings(
                $reflection->getParentClass(), true)
            );
        }

        return $strings;
    }

    /**
     * Register translation string and fire event.
     *
     * @param string $filename
     * @param string $line
     * @param string $bundle
     * @param string $string
     * @param string $class
     * @event string($payload)
     */
    private function register($filename, $line, $bundle, $string, $class = '')
    {
        $payload = compact('filename', 'line', 'bundle', 'string', 'class');

        if ($class) {
            $this->logger()->info("'{string}' found in class '{class}'.", $payload);
        } else {
            $this->logger()->info(
                "'{string}' found in bundle '{bundle}' used in '{filename}' at line {line}.",
                $payload
            );
        }

        $this->bundles[$bundle][] = $this->fire('string', $payload);
    }
}