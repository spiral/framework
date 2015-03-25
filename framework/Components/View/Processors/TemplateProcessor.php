<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Processors;

use Spiral\Components\Files\FileManager;
use Spiral\Components\View\ProcessorInterface;
use Spiral\Components\View\Processors\Templater\ImportAlias;
use Spiral\Components\View\ViewManager;
use Spiral\Components\View\ViewException;
use Spiral\Components\View\Processors\Templater\Node;
use Spiral\Core\Component;
use Spiral\Helpers\ArrayHelper;
use Spiral\Support\Html\Tokenizer;
use Spiral\Components\View\Processors\Templater\Behaviour;
use Spiral\Components\View\Processors\Templater\SupervisorInterface;

class TemplateProcessor implements ProcessorInterface, SupervisorInterface
{
    /**
     * Prefixes to ignore includes and force original html tags.
     */
    const FORCE_HTML = 'forceHTML';

    /**
     * Aliases options (directories and views) will be stored under this key.
     */
    const ALIASES = 'aliases';

    /**
     * Templater rendering options and names.
     *
     * @var array
     */
    protected $options = array(
        'separator' => '.',
        'prefixes'  => array(
            self::FORCE_HTML  => array('html:', '/html:'),
            Node::TYPE_BLOCK  => array('block:', 'section:'),
            Node::TYPE_EXTEND => array('extend:', 'extends:'),
        ),
        'keywords'  => array(
            'namespace'   => array('view:namespace', 'node:namespace'),
            'view'        => array('view::parent', 'node:parent'),
            self::ALIASES => array(
                'name'      => array('import', 'use', 'alias'),
                'pattern'   => array('view', 'tag', 'views', 'folder', 'directory'),
                'namespace' => array('namespace'),
                'prefix'    => array('prefix'),
                'alias'     => array('as')
            ),
            'include'     => array(
                'include'
            )
        )
    );

    /**
     * Parsed and cached view sources to speed up rendering.
     *
     * @var array
     */
    protected $cache = array();

    /**
     * View component instance.
     *
     * @var ViewManager
     */
    protected $view = null;

    /**
     * File component.
     *
     * @var FileManager
     */
    protected $file = null;

    /**
     * New processors instance with options specified in view config.
     *
     * @param array       $options
     * @param ViewManager $view View component instance (if presented).
     * @param FileManager $file
     */
    public function __construct(array $options, ViewManager $view = null, FileManager $file = null)
    {
        $this->options = $options + $this->options;
        Node::setSupervisor($this);

        $this->view = $view;
        $this->file = $file;
    }

    /**
     * Templating engine based on extending and importing blocks.
     *
     * @param string $source    View source (code).
     * @param string $view      View name.
     * @param string $namespace View namespace.
     * @return string
     */
    public function processSource($source, $view, $namespace)
    {
        //Root node based on current view data
        $root = new Node('root', $source, compact('namespace', 'view'));

        return $root->compile();
    }

    /**
     * Get token behaviour. Return null or empty if token has to be removed from rendering.
     *
     * Behaviours separated by 3 types:
     *
     * import  - following token is importing another node
     * block   - token describes node block which can be extended
     * extends - request to extend current node from known parent node.
     *
     * To keep token without defining behaviour - return true.
     *
     * @param array $token
     * @param Node  $node Currently active node.
     * @return mixed|Behaviour
     * @throws ViewException
     */
    public function describeToken(&$token, Node $node = null)
    {
        $tokenName = $token[Tokenizer::TOKEN_NAME];

        $attributes = isset($token[Tokenizer::TOKEN_ATTRIBUTES])
            ? $token[Tokenizer::TOKEN_ATTRIBUTES]
            : array();

        $behaviour = 'keep-token';
        foreach ($this->options['prefixes'] as $type => $prefixes)
        {
            foreach ($prefixes as $prefix)
            {
                if (strpos($tokenName, $prefix) === 0)
                {
                    $name = substr($tokenName, strlen($prefix));
                    switch ($type)
                    {
                        case Node::TYPE_BLOCK:
                            if (!$name)
                            {
                                throw $this->clarifyException(
                                    new ViewException("Every block definition should have name."),
                                    $token[Tokenizer::TOKEN_CONTENT],
                                    $node->options
                                );
                            }

                            //Token describes single block (passing context to child)
                            $behaviour = new Behaviour(
                                $name,
                                Node::TYPE_BLOCK,
                                $attributes,
                                $node->options
                            );

                            break;

                        case Node::TYPE_EXTEND:
                            //Fetching location of node to be imported
                            $nodeContext = $this->fetchContext($name, $attributes, $node->options);

                            //Token describes extended syntax
                            $behaviour = new Behaviour(
                                $name,
                                Node::TYPE_EXTEND,
                                $attributes,
                                array()
                            );

                            //Loading parent node content (namespace either forced, or same as in original node)
                            $content = $this->loadContent(
                                $nodeContext['namespace'],
                                $nodeContext['view'],
                                $token[Tokenizer::TOKEN_CONTENT],
                                $node->options
                            );

                            //This is another namespace than node
                            $behaviour->contextNode = new Node(
                                'root',
                                $content,
                                $nodeContext + array(self::ALIASES => array())
                            );

                            //Import options has to be merged before parent will be extended, make
                            //sure extend is first construction in file/block
                            if (!empty($behaviour->contextNode->options[self::ALIASES]))
                            {
                                /**
                                 * @var ImportAlias $alias
                                 */
                                foreach ($behaviour->contextNode->options[self::ALIASES] as $alias)
                                {
                                    $node->options[self::ALIASES][] = $alias->getCopy(+1);
                                }
                            }
                            break;

                        case self::FORCE_HTML:
                            $token[Tokenizer::TOKEN_NAME] = $name;
                            $tokenType = $token[Tokenizer::TOKEN_TYPE];

                            if ($tokenType == Tokenizer::TAG_OPEN || $tokenType == Tokenizer::TAG_SHORT)
                            {
                                $token[Tokenizer::TOKEN_CONTENT] = '<' . substr(
                                        $token[Tokenizer::TOKEN_CONTENT],
                                        strlen($prefix) + 1
                                    );
                            }
                            else
                            {
                                $token[Tokenizer::TOKEN_CONTENT] = '</' . substr(
                                        $token[Tokenizer::TOKEN_CONTENT],
                                        strlen($prefix) + 2
                                    );
                            }

                            return true;
                    }
                }
            }
        }

        if ($behaviour instanceof Behaviour)
        {
            return $behaviour;
        }

        if ($token[Tokenizer::TOKEN_TYPE] == Tokenizer::TAG_CLOSE)
        {
            //Nothing to do with close tags
            return true;
        }

        /**
         * Processing aliases and imports. Aliases should be used before any block defined and be at
         * 1st level, in other scenario alias will be applied for block and it's content only.
         */
        if (in_array($token[Tokenizer::TOKEN_NAME], $this->options['keywords'][self::ALIASES]['name']))
        {
            $options = array();
            foreach ($this->options['keywords'][self::ALIASES] as $keyword => $names)
            {
                foreach ($names as $attributeName)
                {
                    if (isset($token[Tokenizer::TOKEN_ATTRIBUTES][$attributeName]))
                    {
                        $options[$keyword] = $token[Tokenizer::TOKEN_ATTRIBUTES][$attributeName];
                    }
                }
            }

            try
            {
                $alias = new ImportAlias($node->getLevel(), $options + $node->options);
                $alias->generateAliases($this->view, $this->options['separator']);
            }
            catch (ViewException $exception)
            {
                throw $this->clarifyException(
                    $exception,
                    $token[Tokenizer::TOKEN_CONTENT],
                    $node->options
                );
            }

            $node->options[self::ALIASES][] = $alias;
            ArrayHelper::stableSort(
                $node->options[self::ALIASES],
                function (ImportAlias $optionA, ImportAlias $optionB)
                {
                    return $optionA->level >= $optionB->level;
                }
            );

            //Nothing to render
            return false;
        }

        /**
         * Do not allow automatic namespace import?
         */
        $includeContext = null;
        if (strpos($tokenName, $this->options['separator']) !== false && strpos($tokenName, ':') === false)
        {
            $includeContext = $this->fetchContext($tokenName, $attributes, $node->options);
        }

        //Checking if we need to load this token
        if (!empty($node->options[self::ALIASES]))
        {
            foreach ($node->options[self::ALIASES] as $alias)
            {
                $aliases = $alias->generateAliases($this->view, $this->options['separator']);

                /**
                 * @var ImportAlias $alias
                 */
                if (isset($aliases[$tokenName]))
                {
                    $includeContext = array(
                        'namespace' => $alias->getNamespace(),
                        'view'      => $aliases[$tokenName]
                    );

                    break;
                }
            }
        }

        if (!empty($includeContext))
        {
            $behaviour = new Behaviour(
                $tokenName,
                Node::TYPE_INCLUDE,
                $attributes,
                $node->options
            );

            //Include node, all blocks inside current import namespace
            $behaviour->contextNode = new Node(null, false, $node->options);

            //Include parent (what we including) has it's own context
            try
            {
                $content = $this->loadContent(
                    $includeContext['namespace'],
                    $includeContext['view'],
                    $token[Tokenizer::TOKEN_CONTENT],
                    $node->options
                );

                $behaviour->contextNode->parent = new Node(null, $content, $includeContext);
            }
            catch (ViewException $exception)
            {
                $exception = $this->clarifyException(
                    $exception,
                    $token[Tokenizer::TOKEN_CONTENT],
                    $node->options
                );

                //There is no need to force exception if import not loaded, but we can log it
                $this->view->logger()->error(
                    "{message} in {file} at line {line} defined by '{tokenName}'",
                    array(
                        'message'   => $exception->getMessage(),
                        'file'      => $exception->getFile(),
                        'line'      => $exception->getLine(),
                        'tokenName' => $tokenName
                    )
                );

                return $token;
            }
        }

        //Uses and aliases
        return $behaviour;
    }

    /**
     * Fetch imported or extended node context (view name and namespace).
     *
     * @param string $name        Token name, can include namespaces separated with :, view path
     *                            should be separated using $this->options['separator']
     * @param array  $attributes  Token attributes, can include namespace declaration.
     * @param array  $viewContext Context (location) where this token were called from.
     * @return array
     */
    protected function fetchContext($name, array $attributes, array $viewContext)
    {
        $namespace = $viewContext['namespace'];
        $view = str_replace($this->options['separator'], '/', $name);

        if (strpos($view, ':') !== false)
        {
            //Namespace can be redefined by tag name
            list($namespace, $view) = explode(':', $view);
            if (!$namespace)
            {
                $namespace = $viewContext['namespace'];
            }
        }

        foreach ($attributes as $attribute => $value)
        {
            if (in_array($attribute, $this->options['keywords']['namespace']))
            {
                //Namespace can be redefined from attribute
                $namespace = $value;
            }

            if (in_array($attribute, $this->options['keywords']['view']))
            {
                //Overwriting view
                $view = $value;
            }
        }

        return compact('namespace', 'view');
    }

    /**
     * Load node content based on provided name, type and attributes. Content will be pre-processed
     * with processors declared before templater in processors chain.
     *
     * @param string $namespace
     * @param string $view
     * @param string $tokenContent Token content.
     * @param array  $viewContext  Parent view definition (view and namespace).
     * @return array
     * @throws ViewException
     * @throws \Exception
     */
    protected function loadContent($namespace, $view, $tokenContent = '', array $viewContext = array())
    {
        //Cache name
        $cached = md5($namespace . '-' . $view);
        if (isset($this->cache[$cached]))
        {
            return $this->cache[$cached];
        }

        try
        {
            //View without caching
            $source = $this->file->read($this->view->getFilename($namespace, $view, false));
        }
        catch (ViewException $exception)
        {
            throw $this->clarifyException($exception, $tokenContent, $viewContext);
        }

        /**
         * Some processors should be called before templater, we have to keep this chain.
         */
        foreach ($this->view->getProcessors() as $processor)
        {
            if ($this->view->getProcessor($processor) == $this)
            {
                break;
            }

            $source = $this->view->getProcessor($processor)->processSource($source, $view, $namespace);
        }

        //We can parse tokens before sending to Node, this will speed-up processing
        return $this->cache[$cached] = Tokenizer::parseSource($source);
    }

    /**
     * Clarify exception with correct token location.
     *
     * @param ViewException $exception    Original ViewException without specified tag location.
     * @param string        $tokenContent Token caused parsing or importing error.
     * @param array         $viewContext  Current node context, view, namespace, origin.
     * @return ViewException
     */
    protected function clarifyException(ViewException $exception, $tokenContent, array $viewContext)
    {
        $filename = $this->view->getFilename(
            $viewContext['namespace'],
            $viewContext['view'],
            false
        );

        //Current view source and filename
        $source = file($filename);

        $foundLine = 0;
        foreach ($source as $lineNumber => $line)
        {
            //We found where token were used
            if (strpos($line, $tokenContent) !== false)
            {
                $foundLine = $lineNumber + 1;
                break;
            }
        }

        if ($foundLine)
        {
            $exception->setLocation($filename, $foundLine);
        }

        return $exception;
    }
}