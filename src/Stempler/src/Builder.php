<?php

declare(strict_types=1);

namespace Spiral\Stempler;

use Spiral\Stempler\Compiler\Result;
use Spiral\Stempler\Exception\CompilerException;
use Spiral\Stempler\Exception\ContextExceptionInterface;
use Spiral\Stempler\Exception\LoaderException;
use Spiral\Stempler\Exception\ParserException;
use Spiral\Stempler\Lexer\StringStream;
use Spiral\Stempler\Lexer\Token;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Loader\Source;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\Parser\Context;

/**
 * Builds and compiles templates using set given compiler. Template is passed thought the set of
 * visitors each within specific group.
 */
final class Builder
{
    // node visiting stages
    public const STAGE_PREPARE   = 0;
    public const STAGE_TRANSFORM = 1;
    public const STAGE_FINALIZE  = 2;
    public const STAGE_COMPILE   = 3;

    /** @var VisitorInterface[][] */
    private array $visitors = [];

    public function __construct(
        private readonly LoaderInterface $loader,
        private readonly Parser $parser = new Parser(),
        private readonly Compiler $compiler = new Compiler()
    ) {
    }

    public function getLoader(): LoaderInterface
    {
        return $this->loader;
    }

    public function getParser(): Parser
    {
        return $this->parser;
    }

    public function getCompiler(): Compiler
    {
        return $this->compiler;
    }

    /**
     * Add visitor to specific builder stage.
     */
    public function addVisitor(VisitorInterface $visitor, int $stage = self::STAGE_PREPARE): void
    {
        $this->visitors[$stage][] = $visitor;
    }

    /**
     * Compile template.
     *
     * @throws CompilerException
     * @throws \Throwable
     */
    public function compile(string $path): Result
    {
        $tpl = $this->load($path);

        return $this->compileTemplate($tpl);
    }

    /**
     * @throws ContextExceptionInterface
     * @throws \Throwable
     */
    public function compileTemplate(Template $tpl): Result
    {
        try {
            if (isset($this->visitors[self::STAGE_COMPILE])) {
                $traverser = new Traverser($this->visitors[self::STAGE_COMPILE]);
                $tpl = $traverser->traverse([$tpl])[0];
            }

            return $this->compiler->compile($tpl);
        } catch (CompilerException $e) {
            throw $this->mapException($e);
        }
    }

    /**
     * @throws \Throwable
     */
    public function load(string $path): Template
    {
        $source = $this->loader->load($path);
        $stream = new StringStream($source->getContent());

        try {
            $tpl = $this->parser->withPath($path)->parse($stream);
            $tpl->setContext(new Context(
                new Token(Token::TYPE_RAW, 0, ''),
                $path
            ));
        } catch (ParserException $e) {
            throw $this->mapException($e);
        }

        try {
            return $this->process($tpl);
        } catch (ContextExceptionInterface $e) {
            throw $this->mapException($e);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * @throws \Throwable
     */
    private function process(Template $template): Template
    {
        if (isset($this->visitors[self::STAGE_PREPARE])) {
            $traverser = new Traverser($this->visitors[self::STAGE_PREPARE]);
            $template = $traverser->traverse([$template])[0];
        }

        if (isset($this->visitors[self::STAGE_TRANSFORM])) {
            $traverser = new Traverser($this->visitors[self::STAGE_TRANSFORM]);
            $template = $traverser->traverse([$template])[0];
        }

        if (isset($this->visitors[self::STAGE_FINALIZE])) {
            $traverser = new Traverser($this->visitors[self::STAGE_FINALIZE]);
            $template = $traverser->traverse([$template])[0];
        }

        return $template;
    }

    /**
     * Set exception path and line.
     */
    private function mapException(ContextExceptionInterface $e): ContextExceptionInterface
    {
        if ($e->getContext()->getPath() === null) {
            return $e;
        }

        try {
            $source = $this->loader->load($e->getContext()->getPath());
        } catch (LoaderException) {
            return $e;
        }

        if ($source->getFilename() === null) {
            return $e;
        }

        $e->setLocation(
            $source->getFilename(),
            Source::resolveLine($source->getContent(), $e->getContext()->getToken()->offset)
        );

        return $e;
    }
}
