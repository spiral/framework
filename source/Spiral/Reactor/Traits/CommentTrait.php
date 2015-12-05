<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\Traits;

use Spiral\Reactor\DocComment;

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
     * @param string $comment
     */
    private function initComment($comment)
    {
        if (!empty($comment)) {
            if (is_array($comment)) {
                $this->docComment->setLines($comment);
            } elseif (is_string($comment)) {
                $this->docComment->setString($comment);
            }
        }
    }
}