<?php

namespace App\Shop\Comments;

use Illuminate\Support\Collection;
use App\Shop\Base\BaseRepository;
use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\Comments\Comment;
use App\Shop\Orders\Order;
use App\Shop\Comments\CommentRepository;

class OrderCommentRepository extends BaseRepository implements BaseRepositoryInterface {

    /**
     * UserRepository constructor.
     *
     * @param Post $post
     */
    public function __construct(Order $order) {
        parent::__construct($order);
        $this->model = $order;
    }

    /**
     * @param array $data
     *
     * @return Comment
     */
    public function createComment(array $data): Comment {
        $comment = $this->model->comments()->save(new Comment($data));
        $commentRepo = new CommentRepository(new Comment);
        return $commentRepo->find($comment->id);
    }

    /**
     * @return Collection
     */
    public function listComments(): Collection {
        return $this->model->comments()->getResults();
    }

}
