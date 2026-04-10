<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModerationReview extends Model
{
    use HasFactory;

    public $timestamps = false; // We only have created_at handled manually or by DB

    protected $fillable = [
        'content_type',
        'content_id',
        'reviewer_user_id',
        'action',
        'comments'
    ];

    protected $dates = [
        'created_at'
    ];

    public function content()
    {
        return $this->morphTo(__FUNCTION__, 'content_type', 'content_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}
