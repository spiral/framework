<?php

declare(strict_types=1);

namespace Spiral\Stempler\Parser\Syntax;

use Spiral\Stempler\Lexer\Grammar\DynamicGrammar;
use Spiral\Stempler\Lexer\StringStream;
use Spiral\Stempler\Lexer\Token;
use Spiral\Stempler\Node\Dynamic\Directive;
use Spiral\Stempler\Node\Dynamic\Output;
use Spiral\Stempler\Parser;
use Spiral\Stempler\Parser\Assembler;
use Spiral\Stempler\Parser\SyntaxInterface;

/**
 * Dynamic directives and echo statements.
 */
final class DynamicSyntax implements SyntaxInterface
{
    private ?Directive $directive = null;
    private ?Output $output = null;

    public function handle(Parser $parser, Assembler $asm, Token $token): void
    {
        switch ($token->type) {
            case DynamicGrammar::TYPE_DIRECTIVE:
                $this->directive = new Directive(new Parser\Context($token, $parser->getPath()));
                $asm->push($this->directive);
                break;

            case DynamicGrammar::TYPE_OPEN_TAG:
                $this->output = new Output(new Parser\Context($token, $parser->getPath()));

                $asm->push($this->output);
                break;

            case DynamicGrammar::TYPE_OPEN_RAW_TAG:
                $this->output = new Output(new Parser\Context($token, $parser->getPath()));
                $this->output->rawOutput = true;

                $asm->push($this->output);
                break;

            case DynamicGrammar::TYPE_CLOSE_RAW_TAG:
            case DynamicGrammar::TYPE_CLOSE_TAG:
                $this->output = null;
                break;

            case DynamicGrammar::TYPE_KEYWORD:
                if ($this->directive !== null) {
                    $this->directive->name = $token->content;
                }
                break;

            case DynamicGrammar::TYPE_BODY:
                if ($this->directive !== null) {
                    $this->directive->body = $token->content;
                    $this->directive->values = $this->fetchValues($this->directive->body);

                    $this->directive = null;
                }

                if ($this->output !== null) {
                    $this->output->body = $token->content;
                }

                break;
        }
    }

    /**
     * Parse directive body and split it into values.
     */
    private function fetchValues(string $body): array
    {
        $values = [0 => ''];
        $level = 0;

        $src = new StringStream($body);

        while ($n = $src->peak()) {
            if (\in_array($n, ['"', "'"], true)) {
                $values[\count($values) - 1] .= $n;
                while (($nn = $src->peak()) !== null) {
                    $values[\count($values) - 1] .= $nn;
                    if ($nn === $n) {
                        break;
                    }
                }
                continue;
            }

            if ($n === ',' && $level === 0) {
                $values[] = '';
                continue;
            }

            $values[\count($values) - 1] .= $n;

            if ($n === '(' || $n === '[' || $n === '{') {
                $level++;
                continue;
            }

            if ($n === ')' || $n === ']' || $n === '}') {
                $level--;
            }
        }

        return \array_map('trim', $values);
    }
}
