<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

use Spiral\Reactor\Partial\Comment;

/**
 * Element can have doc comment.
 */
trait CommentTrait
{
    /**
     * @var Comment
     */
    private $docComment;

    /**
     * Get associated file comment.
     */
    public function getComment(): Comment
    {
        return $this->docComment;
    }

    /**
     * Set comment value.
     *
     * @param string|array $comment
     *
     * @return $this
     */
    public function setComment($comment): self
    {
        if (!empty($comment)) {
            if (is_array($comment)) {
                $this->docComment->setLines($comment);
            } elseif (is_string($comment)) {
                $this->docComment->setString($comment);
            }
        }

        return $this;
    }

    /**
     * Init comment value.
     *
     * @param string|array $comment
     */
    private function initComment($comment): void
    {
        if (empty($this->docComment)) {
            $this->docComment = new Comment();
        }

        $this->setComment($comment);
    }
}
