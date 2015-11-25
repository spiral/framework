<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Translator;

use Spiral\Core\Component;
use Spiral\Debug\Traits\LoggerTrait;
use Spiral\Tokenizer\ClassLocatorInterface;
use Spiral\Tokenizer\InvocationLocatorInterface;
use Spiral\Tokenizer\Reflections\ReflectionArgument;
use Spiral\Tokenizer\Reflections\ReflectionInvocation;
use Spiral\Translator\Traits\TranslatorTrait;

/**
 * Index available classes and function calls to fetch every used string translation. Can
 * understand l, p and translate (trait) function.
 *
 * In addition Indexer will find every string specified in default value of model or class which
 * uses TranslatorTrait. String has to be embraced with [[ ]] in order to be indexed, you can
 * disable property indexation using @do-not-index doc comment. Translator can merge strings with
 * parent data, set class constant INHERIT_TRANSLATIONS to true.
 */
class Indexer extends Component
{
    /**
     * Provides event "string" when new string is found.
     */
    use LoggerTrait;

    /**
     * @var Translator
     */
    protected $translator = null;

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Indexing available method and function invocations, target: l, p, $this->translate()
     * functions.
     *
     * @param InvocationLocatorInterface $locator
     */
    public function indexInvocations(InvocationLocatorInterface $locator)
    {
        $this->logger()->info("Indexing invocations of 'l' function.");
        $this->registerInvocations($locator->getInvocations(
            new \ReflectionFunction('l')
        ));

        $this->logger()->info("Indexing invocations of 'p' function.");
        $this->registerInvocations($locator->getInvocations(
            new \ReflectionFunction('p')
        ));

        $this->logger()->info("Indexing invocations of 'translate' method (TranslatorTrait).");
        $this->registerInvocations($locator->getInvocations(
            new \ReflectionMethod(TranslatorTrait::class, 'translate')
        ));
    }

    /**
     * Index and register i18n string located in default properties which belongs to TranslatorTrait
     * classes.
     *
     * @param ClassLocatorInterface $locator
     */
    public function indexClasses(ClassLocatorInterface $locator)
    {
        foreach ($locator->getClasses(TranslatorTrait::class) as $class => $options) {
            $reflection = new \ReflectionClass($class);

            $strings = $this->fetchStrings(
                $reflection,
                $reflection->getConstant('INHERIT_TRANSLATIONS')
            );

            if (!empty($strings)) {
                $this->logger()->info("Found translation string(s) in class '{class}'.", [
                    'class' => $reflection->getName()
                ]);
            }

            foreach ($strings as $string) {
                $this->translator->translate($reflection->getName(), $string);
                $this->logger()->debug("Found '{string}'", compact('string'));
            }
        }
    }

    /**
     * Register found invocations in translator bundles.
     *
     * @param ReflectionInvocation[] $invocations
     */
    protected function registerInvocations(array $invocations)
    {
        foreach ($invocations as $invocation) {
            if ($invocation->countArguments() < 1) {
                //This is not valid invocation
                continue;
            }

            if ($invocation->argument(0)->getType() != ReflectionArgument::STRING) {
                //We can only index invocations with constant string arguments
                continue;
            }

            $this->logger()->debug("Found invocation of '{invocation}' in '{file}' at line {line}.",
                [
                    'invocation' => $invocation->getName(),
                    'file'       => $invocation->getFilename(),
                    'line'       => $invocation->getLine()
                ]);

            $string = $invocation->argument(0)->stringValue();

            if ($invocation->getName() == 'translate') {
                $string = $this->removeBraces($string);
            }

            switch ($invocation->getName()) {
                case 'l':
                    //Translation using default bundle
                    $this->translator->translate(TranslatorInterface::DEFAULT_BUNDLE, $string);
                    break;
                case 'p':
                    //Plural phrase
                    $this->translator->pluralize($string, 0);
                    break;
                case 'translate':
                    //Invocation of TranslatorTrait method
                    $this->translator->translate($invocation->getClass(), $string);
                    break;
            }

            $this->logger()->debug("Found '{string}'", compact('string'));
        }
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
            if (is_string($value) && $this->hasBraces($value)) {
                //Registering string
                $strings[] = $this->removeBraces($value);
            }
        });

        if ($recursively && $reflection->getParentClass()) {
            $strings = array_merge(
                $strings,
                $this->fetchStrings($reflection->getParentClass(), true)
            );
        }

        return $strings;
    }

    /**
     * Remove [[ and ]] braces from translated string.
     *
     * @param string $string
     * @return string
     */
    private function removeBraces($string)
    {
        if ($this->hasBraces($string)) {
            //This string was defined in class attributes
            $string = substr($string, 2, -2);
        }

        return $string;
    }

    /**
     * Check if string has translation braces [[ and ]]/
     *
     * @param string $string
     * @return bool
     */
    private function hasBraces($string)
    {
        return substr($string, 0, 2) == Translator::I18N_PREFIX
        && substr($string, -2) == Translator::I18N_POSTFIX;
    }
}