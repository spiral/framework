<?php

declare(strict_types=1);

namespace Spiral\Stempler\Lexer;

/**
 * Creates local buffers over byte/token stream. Able to replay some tokens.
 *
 * @implements \IteratorAggregate<array-key, Byte|Token|null>
 */
final class Buffer implements \IteratorAggregate
{
    /** @var Byte[]|Token[] */
    private array $buffer = [];

    /** @var Byte[]|Token[] */
    private array $replay = [];

    public function __construct(
        /** @internal */
        private readonly \Generator $generator,
        private int $offset = 0
    ) {
    }

    /**
     * Delegate generation to the nested generator and collect
     * generated token/char stream.
     *
     * @return \Generator<array-key, Byte|Token|null>
     */
    public function getIterator(): \Traversable
    {
        while ($n = $this->next()) {
            yield $n;
        }
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function next(): Byte|Token|null
    {
        if ($this->replay !== []) {
            $n = \array_shift($this->replay);
        } else {
            $n = $this->generator->current();
            if ($n === null) {
                return null;
            }
            $this->generator->next();
            $this->buffer[] = $n;
        }

        if ($n !== null && $n->offset !== null) {
            $this->offset = $n->offset;
        }

        return $n;
    }

    /**
     * Get all the string content until first token.
     */
    public function nextBytes(): string
    {
        $result = '';
        while ($n = $this->next()) {
            if ($n instanceof Byte) {
                $result .= $n->char;
            } else {
                break;
            }
        }

        return $result;
    }

    /**
     * Get next generator value without advancing the position.
     */
    public function lookahead(): Byte|Token|null
    {
        if ($this->replay !== []) {
            return $this->replay[0];
        }

        $n = $this->next();
        if ($n !== null) {
            \array_unshift($this->replay, $n);
        }

        return $n;
    }

    /**
     * Get next byte(s) value if any.
     *
     * @param int $size Size of lookup string.
     */
    public function lookaheadByte(int $size = 1): string
    {
        $result = '';
        $replay = [];
        for ($i = 0; $i < $size; $i++) {
            $n = $this->next();
            if ($n !== null) {
                $replay[] = $n;
            }

            if (!$n instanceof Byte) {
                break;
            }

            $result .= $n->char;
        }

        foreach (\array_reverse($replay) as $n) {
            \array_unshift($this->replay, $n);
        }

        return $result;
    }

    /**
     * Replay all the byte and token stream after given offset.
     */
    public function replay(int $offset): void
    {
        foreach ($this->buffer as $n) {
            if ($n->offset > $offset) {
                $this->replay[] = $n;
            }
        }
    }
}
