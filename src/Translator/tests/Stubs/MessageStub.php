<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Translator\Stubs;

use Spiral\Translator\Traits\TranslatorTrait;

class MessageStub
{
    use TranslatorTrait;

    private $messages = [
        '[[some-text]]'
    ];
}
