<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\SendIt;

use Spiral\Mailer\MessageInterface;
use Symfony\Component\Mime\Email;

interface RendererInterface
{
    /**
     * @param MessageInterface $message
     * @return Email
     */
    public function render(MessageInterface $message): Email;
}
