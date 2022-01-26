<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

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
    private $writers = [];

    public function addWriter(WriterInterface $writer): void
    {
        $this->writers[] = $writer;
    }

    /**
     * Compile the source constrains based on a given specification. Returns altered source.
     *
     * @param mixed                  $source
     * @return mixed|null
     * @throws CompilerException
     */
    public function compile($source, SpecificationInterface ...$specifications)
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

            throw new CompilerException(sprintf(
                'Unable to compile specification `%s` for `%s`, no compiler found',
                get_class($specification),
                is_object($source) ? get_class($source) : gettype($source)
            ));
        }

        return $source;
    }
}
