<?php declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\App\Checker;

use Spiral\Validation\AbstractChecker;

class MyChecker extends AbstractChecker
{
    /**
     * Error messages associated with checker method by name.
     */
    const MESSAGES = [
        'abc' => 'Not ABC'
    ];

    public function abc(string $value): bool
    {
        return $value === 'abc';
    }
}
