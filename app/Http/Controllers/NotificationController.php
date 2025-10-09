<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index()
    {
        // Generate notifikasi sebelum tampil
        $this->generateNotifications();

        $notifications = Notification::forUser(Auth::id())
            ->with('task')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadCount = Notification::forUser(Auth::id())
            ->unread()
            ->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function api()
    {
        $this->generateNotifications();

        $notifications = Notification::forUser(Auth::id())
            ->with('task')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->title,
                    'message' => $notif->message,
                    'type' => $notif->type,
                    'is_read' => $notif->is_read,
                    'time_ago' => $notif->created_at->diffForHumans(),
                    'url' => $notif->task_id ? route('tasks.edit', $notif->task_id) : '#',
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Notification::forUser(Auth::id())->unread()->count()
        ]);
    }

    public function unreadCount()
    {
        $this->generateNotifications();

        $count = Notification::forUser(Auth::id())->unread()->count();
        return response()->json(['count' => $count]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::forUser(Auth::id())->findOrFail($id);
        $notification->markAsRead();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Notifikasi ditandai sudah dibaca']);
        }

        return redirect()->back()->with('success', 'Notifikasi ditandai sudah dibaca');
    }

    public function markAllAsRead()
    {
        Notification::forUser(Auth::id())
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        return redirect()->back()->with('success', 'Semua notifikasi ditandai sudah dibaca');
    }

    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $notification->delete();
        return redirect()->back()->with('success', 'Notifikasi berhasil dihapus');
    }

    public function deleteAll()
    {
        Notification::forUser(Auth::id())->delete();
        return redirect()->back()->with('success', 'Semua notifikasi berhasil dihapus');
    }

    public function deleteAllRead()
    {
        Notification::forUser(Auth::id())->where('is_read', true)->delete();
        return redirect()->back()->with('success', 'Semua notifikasi yang sudah dibaca berhasil dihapus');
    }

    /**
     * ===============================
     * BAGIAN LOGIKA NOTIFIKASI - FIXED
     * ===============================
     */
    public function generateNotifications()
    {
        $now = Carbon::now();
        $userId = Auth::id();
    
        $tasks = Task::where('user_id', $userId)
            ->where('status', 'pending')
            ->whereNotNull('due_date')
            ->get();
    
        foreach ($tasks as $task) {
            // Pastikan due_date tidak null dan valid
            if (!$task->due_date) {
                continue;
            }

            try {
                $dueDate = Carbon::parse($task->due_date);
                $daysDiff = $now->copy()->startOfDay()->diffInDays($dueDate->copy()->startOfDay(), false);
            } catch (\Exception $e) {
                // Skip task jika parsing tanggal gagal
                continue;
            }
    
            $user = $task->user;
            $description = $task->description ?? 'Belum ada deskripsi untuk tugas ini.';
    
            // ================================
            // 🔔 Notifikasi H-5 s.d H-1
            // ================================
            if ($daysDiff >= 1 && $daysDiff <= 5) {
                $type = "deadline_h{$daysDiff}";
                $title = "⏰ Deadline H-{$daysDiff}";

                // Format pesan lebih deskriptif
                $message = "📘 <strong>Tugas:</strong> {$task->title}\n" .
                          "📝 <strong>Deskripsi:</strong> {$description}\n" .
                          "🗓️ <strong>Dibuat:</strong> " . $task->created_at->format('d M Y') . "\n" .
                          "⏰ <strong>Deadline:</strong> " . $dueDate->format('d M Y') . "\n" .
                          "⚡ Jangan lupa segera kerjakan tugas ini agar tidak terlambat!";

                $this->createNotificationIfNotExists($task, $type, $title, strip_tags($message));

                // Kirim email jika ada user dan email
                if ($user && $user->email) {
                    try {
                        \Mail::to($user->email)->send(new \App\Mail\DeadlineNotification(
                            $user->name,
                            $task->title,
                            route('tasks.edit', $task->id),
                            $dueDate->format('d M Y'),
                            $daysDiff
                        ));
                    } catch (\Exception $e) {
                        // Log error email tapi jangan stop proses
                        \Log::error('Gagal kirim email notifikasi: ' . $e->getMessage());
                    }
                }
            }

            // ================================
            // 🚨 Deadline Hari Ini
            // ================================
            if ($daysDiff === 0) {
                $type = 'deadline_today';
                $title = '🚨 Deadline Hari Ini!';

                $message = "📘 <strong>Tugas:</strong> {$task->title}\n" .
                          "📝 <strong>Deskripsi:</strong> {$description}\n" .
                          "🗓️ <strong>Dibuat:</strong> " . $task->created_at->format('d M Y') . "\n" .
                          "⏰ <strong>Deadline:</strong> " . $dueDate->format('d M Y') . "\n" .
                          "⚡ Tugas ini harus diselesaikan hari ini!";

                $this->createNotificationIfNotExists($task, $type, $title, strip_tags($message));

                // Kirim email untuk deadline hari ini
                if ($user && $user->email) {
                    try {
                        \Mail::to($user->email)->send(new \App\Mail\DeadlineNotification(
                            $user->name,
                            $task->title,
                            route('tasks.edit', $task->id),
                            $dueDate->format('d M Y'),
                            0
                        ));
                    } catch (\Exception $e) {
                        \Log::error('Gagal kirim email notifikasi deadline hari ini: ' . $e->getMessage());
                    }
                }
            }

            // ================================
            // ⚠️ Tugas Terlambat
            // ================================
            if ($daysDiff < 0) {
                $type = 'task_overdue';
                $title = '⚠️ Tugas Terlambat!';
                $lateDays = abs($daysDiff);

                $message = "📘 <strong>Tugas:</strong> {$task->title}\n" .
                          "📝 <strong>Deskripsi:</strong> {$description}\n" .
                          "🗓️ <strong>Dibuat:</strong> " . $task->created_at->format('d M Y') . "\n" .
                          "⏰ <strong>Deadline:</strong> " . $dueDate->format('d M Y') . "\n" .
                          "🔴 <strong>Status:</strong> Terlambat {$lateDays} hari\n" .
                          "⚡ Segera selesaikan tugas ini!";

                $this->createNotificationIfNotExists($task, $type, $title, strip_tags($message));
            }
        }
    }
    
    /**
     * Membuat notifikasi hanya jika belum ada di hari ini dan tipe sama.
     */
    private function createNotificationIfNotExists($task, $type, $title, $message)
    {
        $today = Carbon::today()->toDateString();
    
        $exists = Notification::where('task_id', $task->id)
            ->where('user_id', $task->user_id)
            ->where('type', $type)
            ->whereDate('created_at', $today)
            ->exists();
    
        if (!$exists) {
            return Notification::create([
                'user_id' => $task->user_id,
                'task_id' => $task->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'created_at' => now(),
            ]);
        }
        
        return null;
    }

    public static function createNewTaskNotification(Task $task)
{
    $description = $task->description ?? 'Belum ada deskripsi untuk tugas ini.';
    
    // Format deadline info dengan jam
    $deadlineInfo = 'Tidak ada deadline';
    if ($task->due_date) {
        try {
            $deadlineInfo = Carbon::parse($task->due_date)->format('d M Y H:i');
        } catch (\Exception $e) {
            $deadlineInfo = 'Deadline tidak valid';
        }
    }

    $message = "📘 <strong>Tugas:</strong> {$task->title}\n" .
              "📝 <strong>Deskripsi:</strong> {$description}\n" .
              "🗓️ <strong>Dibuat:</strong> " . $task->created_at->format('d M Y H:i') . "\n" .
              "⏰ <strong>Deadline:</strong> {$deadlineInfo}\n" .
              "✅ Tugas baru berhasil ditambahkan!";

    Notification::create([
        'user_id' => $task->user_id,
        'task_id' => $task->id,
        'type' => 'new_task',
        'title' => '✨ Tugas Baru Ditambahkan',
        'message' => strip_tags($message),
    ]);
}

    /**
     * Notifikasi untuk tugas selesai
     */
    public static function createTaskCompletedNotification(Task $task)
    {
        Notification::create([
            'user_id' => $task->user_id,
            'task_id' => $task->id,
            'type' => 'task_completed',
            'title' => '🎉 Tugas Selesai!',
            'message' => "Selamat! Tugas '{$task->title}' telah diselesaikan",
        ]);
    }

    /**
     * Notifikasi untuk tugas dihapus
     */
    public static function createTaskDeletedNotification(Task $task)
    {
        Notification::create([
            'user_id' => $task->user_id,
            'task_id' => $task->id,
            'type' => 'task_deleted',
            'title' => '🗑️ Tugas Dihapus',
            'message' => "Tugas '{$task->title}' telah dihapus dari daftar",
        ]);
    }
}