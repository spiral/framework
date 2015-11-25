<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Stempler;

use Spiral\Core\Component;
use Spiral\Stempler\Behaviours\BlockBehaviour;
use Spiral\Stempler\Behaviours\ExtendsBehaviour;
use Spiral\Stempler\Behaviours\IncludeBehaviour;
use Spiral\Stempler\Exceptions\StemplerException;
use Spiral\Stempler\Importers\Stopper;

/**
 * Supervisors used to control node behaviours and syntax.
 */
class Supervisor extends Component implements SupervisorInterface
{
    /**
     * Used to create unique node names when required.
     *
     * @var int
     */
    private static $index = 0;

    /**
     * Active set of imports.
     *
     * @var ImporterInterface[]
     */
    private $importers = [];

    /**
     * @var SyntaxInterface
     */
    protected $syntax = null;

    /**
     * @var LoaderInterface
     */
    protected $loader = null;

    /**
     * @param LoaderInterface $loader
     * @param SyntaxInterface $syntax
     */
    public function __construct($loader, SyntaxInterface $syntax)
    {
        $this->loader = $loader;
        $this->syntax = $syntax;
    }

    /**
     * {@inheritdoc}
     */
    public function syntax()
    {
        return $this->syntax;
    }

    /**
     * Add new elements import locator.
     *
     * @param ImporterInterface $import
     */
    public function registerImporter(ImporterInterface $import)
    {
        array_unshift($this->importers, $import);
    }

    /**
     * Active templater imports.
     *
     * @return ImporterInterface[]
     */
    public function getImporters()
    {
        return $this->importers;
    }

    /**
     * Remove all element importers.
     */
    public function flushImporters()
    {
        $this->importers = [];
    }

    /**
     * {@inheritdoc}
     */
    public function tokenBehaviour(array $token, array $content, Node $node)
    {
        switch ($this->syntax->tokenType($token, $name)) {
            case SyntaxInterface::TYPE_BLOCK:
                //Tag declares block (section)
                return new BlockBehaviour($name);

            case SyntaxInterface::TYPE_EXTENDS:
                //Declares parent extending
                $extends = new ExtendsBehaviour(
                    $this->createNode($this->syntax->resolvePath($token), $token),
                    $token
                );

                //We have to combine parent imports with local one (this is why uses has to be defined
                //after extends tag!)
                $this->importers = $extends->parentImports();

                //Sending command to extend parent
                return $extends;

            case SyntaxInterface::TYPE_IMPORTER:
                //Implementation specific
                $this->registerImporter($this->syntax->createImporter($token, $this));

                //No need to include import tag into source
                return BehaviourInterface::SKIP_TOKEN;
        }

        //We now have to decide if element points to external view (source) to be imported
        foreach ($this->importers as $importer) {
            if ($importer->importable($name, $token)) {
                if ($importer instanceof Stopper) {
                    //Native importer tells us to treat this element as simple html
                    break;
                }

                //Let's include!
                return new IncludeBehaviour(
                    $this, $importer->resolvePath($name, $token), $content, $token
                );
            }
        }

        return BehaviourInterface::SIMPLE_TAG;
    }

    /**
     * Create node based on provided location (source to be fetched from loader). Supervisor
     * automatically clone itself to prevent import collisions.
     *
     * @param string $path
     * @param array  $token Context token.
     * @return Node
     * @throws StemplerException
     */
    public function createNode($path, array $token = [])
    {
        //We support dots!
        if (!empty($token)) {
            $path = str_replace('.', '/', $path);
        }

        try {
            $source = $this->loader->getSource($path);
        } catch (\Exception $exception) {
            throw new StemplerException($exception->getMessage(), $token, 0, $exception);
        }

        try {
            return new Node(clone $this, $this->uniquePlaceholder(), $source);
        } catch (StemplerException $exception) {
            //Wrapping to clarify location of error
            throw $this->clarifyException($path, $exception);
        }
    }

    /**
     * Get unique placeholder name, unique names are required in some cases to correctly process
     * includes and etc.
     *
     * @return string
     */
    public function uniquePlaceholder()
    {
        return md5(self::$index++);
    }

    /**
     * Clarify exeption with it's actual location.
     *
     * @param string            $path
     * @param StemplerException $exception
     * @return StemplerException
     */
    protected function clarifyException($path, StemplerException $exception)
    {
        if (empty($exception->getToken())) {
            //Unable to locate
            return $exception;
        }

        //We will need only first tag line
        $target = explode("\n", $exception->getToken()[HtmlTokenizer::TOKEN_CONTENT])[0];

        //Let's try to locate place where exception was used
        $lines = explode("\n", $this->loader->getSource($path));

        foreach ($lines as $number => $line) {
            if (strpos($line, $target) !== false) {
                //We found where token were used (!!)
                $exception->setLocation($this->loader->localFilename($path), $number + 1);

                break;
            }
        }

        return $exception;
    }
}