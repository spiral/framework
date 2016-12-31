<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Migrations\Migration;

/**
 * Migration meta information specific to current environment
 */
final class State
{
    /**
     * Migration statues.
     */
    const STATUS_UNDEFINED = -1;
    const STATUS_PENDING   = 0;
    const STATUS_EXECUTED  = 1;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var bool
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var \DateTime
     */
    private $timeCreated = null;

    /**
     * @var \DateTime|null
     */
    private $timeExecuted = null;

    /**
     * @param string    $name
     * @param \DateTime $timeCreated
     * @param int       $status
     * @param \DateTime $timeExecuted
     */
    public function __construct(
        string $name,
        \DateTime $timeCreated,
        int $status = self::STATUS_UNDEFINED,
        \DateTime $timeExecuted = null
    ) {
        $this->name = $name;
        $this->status = $status;
        $this->timeCreated = $timeCreated;
        $this->timeExecuted = $timeExecuted;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Migration status.
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Get migration creation time.
     *
     * @return \DateTime
     */
    public function getTimeCreated(): \DateTime
    {
        return $this->timeCreated;
    }

    /**
     * Get migration execution time if any.
     *
     * @return \DateTime|null
     */
    public function getTimeExecuted()
    {
        return $this->timeExecuted;
    }

    /**
     * @param int            $status
     * @param \DateTime|null $timeExecuted
     *
     * @return State
     */
    public function withStatus(int $status, \DateTime $timeExecuted = null): State
    {
        $state = clone $this;
        $state->status = $status;
        $state->timeExecuted = $timeExecuted;

        return $state;
    }
}