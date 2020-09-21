<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Mailer;

interface MessageInterface
{
    /**
     * Get email subject/template to compile email body from. This is not the same as actual email subject.
     *
     * @return string
     */
    public function getSubject(): string;

    /**
     * Get email template variable data.
     *
     * @return array
     */
    public function getData(): array;

    /**
     * List of emails to send email to. Might include long notation.
     *
     * @return array
     */
    public function getTo(): array;

    /**
     * Get list of addresses to send copy to.
     *
     * @return array
     */
    public function getCC(): array;

    /**
     * Get list of address to send blind copy to.
     *
     * @return array
     */
    public function getBCC(): array;

    /**
     * The address to send email from, might include long notation.
     *
     * @return string|null
     */
    public function getFrom(): ?string;

    /**
     * The address to reply to.
     *
     * @return string|null
     */
    public function getReplyTo(): ?string;

    /**
     * Implementation specific options.
     *
     * @return array
     */
    public function getOptions(): array;
}
