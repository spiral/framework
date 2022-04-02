<?php

declare(strict_types=1);

namespace Spiral\Prototype;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Spiral\Prototype\ClassNode\ConflictResolver;
use Spiral\Prototype\Exception\ClassNotDeclaredException;
use Spiral\Prototype\NodeVisitors\ClassNode\DeclareClass;
use Spiral\Prototype\NodeVisitors\ClassNode\LocateStatements;
use Spiral\Prototype\NodeVisitors\ClassNode\LocateVariables;

/**
 * @internal
 */
final class NodeExtractor
{
    private readonly Parser $parser;

    public function __construct(
        private readonly ConflictResolver\Names $namesResolver,
        private readonly ConflictResolver\Namespaces $namespacesResolver,
        Parser $parser = null
    ) {
        $this->parser = $parser ?? (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
    }

    /**
     * @throws ClassNotDeclaredException
     * @throws \ReflectionException
     */
    public function extract(string $filename, array $dependencies): ClassNode
    {
        $definition = $this->makeDefinition($filename);
        $definition->dependencies = $dependencies;

        $stmts = new LocateStatements();
        $vars = new LocateVariables();
        $this->traverse($filename, $stmts, $vars);

        $this->fillStmts($definition, $stmts->getImports());
        $this->fillConstructorParams($definition);
        $this->fillConstructorVars($vars->getVars(), $definition);
        $this->resolveConflicts($definition);

        return $definition;
    }

    /**
     * @throws ClassNotDeclaredException
     */
    private function makeDefinition(string $filename): ClassNode
    {
        $declarator = new DeclareClass();
        $this->traverse($filename, $declarator);

        if (empty($declarator->getClass())) {
            throw new ClassNotDeclaredException($filename);
        }

        if ($declarator->getNamespace()) {
            return ClassNode::createWithNamespace($declarator->getClass(), $declarator->getNamespace());
        }

        return ClassNode::create($declarator->getClass());
    }

    private function traverse(string $filename, NodeVisitor ...$visitors): void
    {
        $tr = new NodeTraverser();

        foreach ($visitors as $visitor) {
            $tr->addVisitor($visitor);
        }

        $tr->traverse($this->parser->parse(\file_get_contents($filename)));
    }

    private function fillStmts(ClassNode $definition, array $imports): void
    {
        foreach ($imports as $import) {
            $definition->addImportUsage($import['name'], $import['alias']);
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function fillConstructorParams(ClassNode $definition): void
    {
        $reflection = new \ReflectionClass(\sprintf('%s\%s', $definition->namespace, $definition->class));

        $constructor = $reflection->getConstructor();
        if ($constructor !== null) {
            $definition->hasConstructor = $constructor->getDeclaringClass()->getName() === $reflection->getName();

            foreach ($reflection->getConstructor()->getParameters() as $parameter) {
                $definition->addParam($parameter);
            }
        }
    }

    /**
     * Collect all variable definitions from constructor method body.
     * Vars which are however also inserted via method are ignored (and still used as constructor params).
     */
    private function fillConstructorVars(array $vars, ClassNode $definition): void
    {
        foreach ($vars as $k => $var) {
            if (isset($definition->constructorParams[$var])) {
                unset($vars[$k]);
            }
        }

        $definition->constructorVars = $vars;
    }

    private function resolveConflicts(ClassNode $definition): void
    {
        $this->namesResolver->resolve($definition);
        $this->namespacesResolver->resolve($definition);
    }
}
