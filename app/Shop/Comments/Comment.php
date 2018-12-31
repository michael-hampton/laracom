<?php
namespace App\Shop\Comments;
use Illuminate\Database\Eloquent\Model;
class Comment extends Model
{
    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'subtype',
        'content',
        'source',
        'ip_address',
        'user_id',
    ];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function commentable()
    {
        return $this->morphTo();
    }
}