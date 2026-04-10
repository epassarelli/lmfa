<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'action_url',
        'is_read',
        'created_at',
        'read_at',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
        'read_at'    => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }

    /**
     * Create a notification for a user.
     */
    public static function notify(int $userId, string $type, string $title, string $body, ?string $actionUrl = null): static
    {
        return static::create([
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'body'       => $body,
            'action_url' => $actionUrl,
        ]);
    }
}
