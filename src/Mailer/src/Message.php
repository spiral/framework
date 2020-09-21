<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Mailer;

final class Message implements MessageInterface
{
    /** @var string */
    private $subject;

    /** @var array */
    private $data;

    /** @var array */
    private $to = [];

    /** @var array */
    private $cc = [];

    /** @var array */
    private $bcc = [];

    /** @var string|null */
    private $from;

    /** @var string|null */
    private $replyTo;

    /** @var array */
    private $options = [];

    /**
     * @param string          $subject
     * @param string|string[] $to
     * @param array           $data
     */
    public function __construct(string $subject, $to, array $data = [])
    {
        $this->setSubject($subject);
        $this->setTo(...(array) $to);
        $this->setData($data);
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param string ...$to
     * @return $this
     */
    public function setTo(string ...$to): self
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @return array
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * @param string ...$cc
     * @return $this
     */
    public function setCC(string ...$cc): self
    {
        $this->cc = $cc;

        return $this;
    }

    /**
     * @return array
     */
    public function getCC(): array
    {
        return $this->cc;
    }

    /**
     * @param string ...$bcc
     * @return $this
     */
    public function setBCC(string ...$bcc): self
    {
        $this->bcc = $bcc;

        return $this;
    }

    /**
     * @return array
     */
    public function getBCC(): array
    {
        return $this->bcc;
    }

    /**
     * @param string|null $from
     * @return $this
     */
    public function setFrom(?string $from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFrom(): ?string
    {
        return $this->from;
    }

    /**
     * @param string|null $replyTo
     * @return $this
     */
    public function setReplyTo(?string $replyTo): self
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return $this
     */
    public function setOption(string $name, $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
