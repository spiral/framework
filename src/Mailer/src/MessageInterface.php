<?php

declare(strict_types=1);

namespace Spiral\Mailer;

interface MessageInterface
{
    /**
     * Get email subject/template to compile email body from. This is not the same as actual email subject.
     */
    public function getSubject(): string;

    /**
     * Get email template variable data.
     */
    public function getData(): array;

    /**
     * List of emails to send email to. Might include long notation.
     */
    public function getTo(): array;

    /**
     * Get list of addresses to send copy to.
     */
    public function getCC(): array;

    /**
     * Get list of address to send blind copy to.
     */
    public function getBCC(): array;

    /**
     * The address to send email from, might include long notation.
     */
    public function getFrom(): ?string;

    /**
     * The address to reply to.
     */
    public function getReplyTo(): ?string;

    /**
     * Implementation specific options.
     */
    public function getOptions(): array;
}
