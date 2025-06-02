<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $user_id
 * @property int $telegram_message_id
 * @property string $title
 * @property string $body
 * @property string $image
 * @property mixed $image_url
 */
class Post extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    protected $fillable = ['title', 'body', 'user_id', 'telegram_message_id', 'image'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }
}
