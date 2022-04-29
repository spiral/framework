<?php

declare(strict_types=1);

namespace Spiral\Prototype\ClassNode\ConflictResolver;

final class Sequences
{
    /**
     * Examples:
     * [], <any> => 0
     *
     * @param int[] $sequences
     */
    public function find(array $sequences, int $originSequence): int
    {
        if (empty($sequences) || $originSequence > \max($sequences)) {
            return $originSequence;
        }

        $gaps = $this->skippedSequences($sequences);

        if (isset($gaps[$originSequence])) {
            return $originSequence;
        }

        //we do not add "1" as postfix: $var, $var2, $var3, etc
        unset($gaps[1]);
        if (empty($gaps)) {
            $max = \max($sequences);
            if ($max === 0) {
                return 2;
            }

            return $max + 1;
        }

        return \min($gaps);
    }

    private function skippedSequences(array $sequences): array
    {
        $skipped = [];
        $max = \max($sequences);
        for ($i = 0; $i < $max; $i++) {
            if (!\in_array($i, $sequences, true)) {
                $skipped[$i] = $i;
            }
        }

        return $skipped;
    }
}
