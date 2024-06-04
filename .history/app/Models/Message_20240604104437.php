<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['from_user_id', 'to_user_id', 'type', 'message'];

    /**
     * Define a many-to-one relationship with the sender of the message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     *
     * This relationship links the message to the user who sent it.
     *
     * Example Usage:
     * $message->fromUser; // Retrieves the user who sent the message.
     */
    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
