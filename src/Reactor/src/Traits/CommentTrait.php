<?php

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

use Spiral\Reactor\Partial\Comment;

/**
 * Element can have doc comment.
 */
trait CommentTrait
{
    private Comment $docComment;

    /**
     * Get associated file comment.
     */
    public function getComment(): Comment
    {
        return $this->docComment;
    }

    /**
     * Set comment value.
     */
    public function setComment(array|string $comment): self
    {
        if (!empty($comment)) {
            if (\is_array($comment)) {
                $this->docComment->setLines($comment);
            } elseif (\is_string($comment)) {
                $this->docComment->setString($comment);
            }
        }

        return $this;
    }

    /**
     * Init comment value.
     */
    private function initComment(array|string $comment): void
    {
        if (empty($this->docComment)) {
            $this->docComment = new Comment();
        }

        $this->setComment($comment);
    }
}
