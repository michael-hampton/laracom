<?php
namespace App\Traits;

use App\Shop\Orders\Order;
/**
 * Description of OrderCommentTrait
 *
 * @author michael.hampton
 */
trait OrderCommentTrait {

public function saveComment(Order $order, string $comment) : bool {
$postRepo = new OrderCommentRepository($order);
 
  $data = [
      'content' => $comment,
      'user_id' => auth()->guard('admin')->user()->id
  ];
           
  $postRepo->createComment($data);
}
}
