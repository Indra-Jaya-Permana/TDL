<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationMail;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'task_id', 'type', 'title', 'message', 'is_read', 'read_at'
    ];

    protected static function booted()
    {
        static::created(function ($notification) {
            // Ambil user penerima
            $user = $notification->user;

            // Pastikan email user ada
            if ($user && $user->email) {
                // Kirim email notifikasi
                Mail::to($user->email)->send(new NotificationMail($notification));
            }
        });
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Task
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // Scope untuk user
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope unread
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }
}
