<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $telegram_message_id
 * @property string $title
 * @property string $body
 */
class Post extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    protected $fillable = ['title', 'body', 'user_id', 'telegram_message_id'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
