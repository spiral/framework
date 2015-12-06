<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\Traits;

use Spiral\Reactor\Body\DocComment;

/**
 * Element can have doc comment.
 */
trait CommentTrait
{
    /**
     * @var DocComment
     */
    private $docComment = null;

    /**
     * Get associated file comment.
     *
     * @return DocComment
     */
    public function comment()
    {
        return $this->docComment;
    }

    /**
     * Initi comment value.
     *
     * @param string|array $comment
     */
    private function initComment($comment)
    {
        if (empty($this->docComment)) {
            $this->docComment = new DocComment();
        }

        $this->setComment($comment);
    }

    /**
     * Set comment value.
     *
     * @param string|array $comment
     * @return $this
     */
    public function setComment($comment)
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
}