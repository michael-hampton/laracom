<?php

namespace App\Traits;

use App\Shop\Orders\Order;
use App\Shop\Comments\OrderCommentRepository;

/**
 * Description of OrderCommentTrait
 *
 * @author michael.hampton
 */
trait OrderCommentTrait {

    /**
     * 
     * @param Order $order
     * @param string $comment
     * @return bool
     */
    public function saveNewComment(Order $order, string $comment): bool {
        $postRepo = new OrderCommentRepository($order);

        $data = [
            'content' => $comment,
            'user_id' => auth()->guard('admin')->user()->id
        ];

        $postRepo->createComment($data);
        
        return true;
    }

}
