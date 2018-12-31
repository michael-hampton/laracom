<?php

namespace App\Shop\Comments;

use App\Shop\Base\BaseRepository;
use App\Shop\Comments\Comment;

class CommentRepository extends BaseRepository {

    /**
     * CommentRepository constructor.
     *
     * @param Comment $comment
     */
    public function __construct(Comment $comment) {
        parent::__construct($comment);
    }

}
