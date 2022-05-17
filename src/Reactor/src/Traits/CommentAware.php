<?php

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

/**
 * @internal
 */
trait CommentAware
{
    public function setComment(array|string|null $comment): static
    {
        if (\is_array($comment)) {
            foreach ($comment as $value) {
                $this->element->addComment($value);
            }

            return $this;
        }

        $this->element->setComment($comment);

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->element->getComment();
    }


    public function addComment(string $comment): static
    {
        $this->element->addComment($comment);

        return $this;
    }
}
