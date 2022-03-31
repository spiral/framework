<?php

declare(strict_types=1);

namespace Spiral\DataGrid;

use Spiral\DataGrid\Exception\CompilerException;
use Spiral\DataGrid\Specification\SequenceInterface;

/**
 * SpecificationWriter writes the specifications into target source using a set of associated compilers.
 */
final class Compiler
{
    /** @var WriterInterface[] */
    private array $writers = [];

    public function addWriter(WriterInterface $writer): void
    {
        $this->writers[] = $writer;
    }

    /**
     * Compile the source constrains based on a given specification. Returns altered source.
     *
     * @throws CompilerException
     */
    public function compile(mixed $source, SpecificationInterface ...$specifications): mixed
    {
        if ($source === null) {
            return null;
        }

        foreach ($specifications as $specification) {
            if ($specification instanceof SequenceInterface) {
                return $this->compile($source, ...$specification->getSpecifications());
            }

            $isWritten = false;
            foreach ($this->writers as $writer) {
                $result = $writer->write($source, $specification, $this);
                if ($result !== null) {
                    $source = $result;
                    $isWritten = true;
                }
            }

            if ($isWritten) {
                continue;
            }

            throw new CompilerException(\sprintf(
                'Unable to compile specification `%s` for `%s`, no compiler found',
                $specification::class,
                \get_debug_type($source)
            ));
        }

        return $source;
    }
}
