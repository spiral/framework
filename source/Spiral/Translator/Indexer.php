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
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\InvocationsInterface;
use Spiral\Tokenizer\Reflections\ReflectionArgument;
use Spiral\Tokenizer\Reflections\ReflectionInvocation;
use Spiral\Translator\Configs\TranslatorConfig;
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
    use LoggerTrait;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var TranslatorConfig
     */
    protected $config;

    /**
     * @var Catalogue
     */
    protected $catalogue;

    /**
     * @param Translator       $translator
     * @param TranslatorConfig $config
     */
    public function __construct(Translator $translator, TranslatorConfig $config)
    {
        $this->translator = $translator;
        $this->config = $config;

        //Indexation into fallback (root) locale
        $this->catalogue = $translator->getCatalogue($config->fallbackLocale());
    }

    /**
     * Indexing available method and function invocations, target: l, p, $this->translate()
     * functions.
     *
     * @param InvocationsInterface $locator
     */
    public function indexInvocations(InvocationsInterface $locator)
    {
        $this->logger()->info("Indexing usages of 'l' function.");
        $this->registerInvocations(
            $locator->getInvocations(new \ReflectionFunction('l'))
        );

        $this->logger()->info("Indexing usages of 'p' function.");
        $this->registerInvocations(
            $locator->getInvocations(new \ReflectionFunction('p'))
        );

        $this->logger()->info("Indexing usages of 'say' method (TranslatorTrait).");
        $this->registerInvocations(
            $locator->getInvocations(new \ReflectionMethod(TranslatorTrait::class, 'say'))
        );

        $this->translator->getCatalogue()->saveDomains();
    }

    /**
     * Index and register i18n string located in default properties which belongs to TranslatorTrait
     * classes.
     *
     * @param ClassesInterface $locator
     */
    public function indexClasses(ClassesInterface $locator)
    {
        foreach ($locator->getClasses(TranslatorTrait::class) as $class => $options) {
            $reflection = new \ReflectionClass($class);

            $strings = $this->fetchStrings(
                $reflection,
                $reflection->getConstant('INHERIT_TRANSLATIONS')
            );

            if (!empty($strings)) {
                $this->logger()->info(
                    "Found translation string(s) in class '{class}'.",
                    ['class' => $reflection->getName()]
                );
            }

            foreach ($strings as $string) {
                $this->register(
                    $this->translator->resolveDomain($reflection->getName()),
                    $string
                );
            }
        }

        $this->catalogue->saveDomains();
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

            $this->logger()->debug(
                "Found invocation of '{invocation}' in '{file}' at line {line}.",
                [
                    'invocation' => $invocation->getName(),
                    'file'       => $invocation->getFilename(),
                    'line'       => $invocation->getLine()
                ]
            );

            $string = $invocation->argument(0)->stringValue();

            if ($invocation->getName() == 'say') {
                $string = $this->removeBraces($string);
            }

            //Translation using default bundle
            $domain = $this->config->defaultDomain();

            if ($invocation->getName() == 'say') {
                $domain = $this->translator->resolveDomain($invocation->getClass());
            } else {
                //L or P functions
                if ($invocation->countArguments() >= 3) {
                    if ($invocation->argument(2)->getType() != ReflectionArgument::STRING) {
                        //Unable to resolve domain name
                        continue;
                    }

                    $domain = $this->translator->resolveDomain(
                        $invocation->argument(2)->stringValue()
                    );
                }
            }

            $this->register($domain, $string);
        }
    }

    /**
     * Register string in active translator.
     *
     * @param string $domain
     * @param string $string
     */
    protected function register($domain, $string)
    {
        //Automatically registering
        $this->catalogue->set($domain, $string, $string);

        $this->logger()->debug("Found [{domain}]:'{string}'", compact('domain', 'string'));
    }

    /**
     * Fetch default string values from class and merge it with parent strings if requested.
     *
     * @param \ReflectionClass $reflection
     * @param bool             $recursively
     *
     * @return array
     */
    private function fetchStrings(\ReflectionClass $reflection, $recursively = false)
    {
        $target = $reflection->getDefaultProperties() + $reflection->getConstants();

        foreach ($reflection->getProperties() as $property) {
            if (strpos($property->getDocComment(), "@do-not-index")) {
                unset($target[$property->getName()]);
            }
        }

        $strings = [];
        array_walk_recursive($target, function ($value) use (&$strings) {
            if (is_string($value) && $this->hasBraces($value)) {
                $strings[] = $this->removeBraces($value);
            }
        });

        if ($recursively && $reflection->getParentClass()) {
            //Joining strings data with parent class values (inheritance ON) - resolved into same
            //domain on export
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
     *
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
     *
     * @return bool
     */
    private function hasBraces($string)
    {
        return substr($string, 0, 2) == Translator::I18N_PREFIX
            && substr($string, -2) == Translator::I18N_POSTFIX;
    }
}