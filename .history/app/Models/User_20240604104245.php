<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
 * Define a one-to-many relationship for messages sent by the user.
 *
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 *
 * This relationship links the user to the messages they have sent.
 *
 * Example Usage:
 * $user->sentMessages; // Retrieves all messages where the user is the sender.
 */
public function sentMessages()
{
    return $this->hasMany(Message::class, 'from_user_id');
}

/**
 * Define a one-to-many relationship for messages received by the user.
 *
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 *
 * This relationship links the user to the messages they have received.
 *
 * Example Usage:
 * $user->receivedMessages; // Retrieves all messages where the user is the recipient.
 */
public function receivedMessages()
{
    return $this->hasMany(Message::class, 'to_user_id');
}

}
