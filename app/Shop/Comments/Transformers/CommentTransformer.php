<?php

namespace App\Shop\Comments\Transformers;

use App\Shop\Comments\Comment;
use App\Shop\Employees\Repositories\EmployeeRepository;
use App\Shop\Employees\Employee;

trait CommentTransformer {

    /**
     * 
     * @param Comment $comment
     * @return Comment
     */
    public function transformComment(Comment $comment) {
        $userRepo = new EmployeeRepository(new Employee);
        $objUser = $userRepo->findEmployeeById($comment->user_id);

        $commentObj = new Comment;


        $commentObj->id = (int) $comment->id;
        $commentObj->content = $comment->content;
        $commentObj->subtype = $comment->subtype;
        $commentObj->source = $comment->source;
        $commentObj->ip_address = $comment->ip_address;
        $commentObj->user = $objUser->name;
        $commentObj->created_at = $comment->created_at;
        $commentObj->updated_at = $comment->updated_at;

        return $commentObj;
    }

}
