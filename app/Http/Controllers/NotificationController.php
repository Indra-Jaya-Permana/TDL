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
                    'url' => $notif->task_id ? route('tasks.show', $notif->task_id) : null,
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
     * BAGIAN LOGIKA NOTIFIKASI
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
            $dueDate = Carbon::parse($task->due_date);
    
            // Hitung selisih hari (positif = sebelum deadline, negatif = setelah)
            $daysDiff = $now->copy()->startOfDay()->diffInDays($dueDate->copy()->startOfDay(), false);
    
            // ================================
            // Notifikasi H-5 s.d H-1
            // ================================
            if ($daysDiff >= 1 && $daysDiff <= 5) {
                $type = "deadline_h{$daysDiff}";
                $title = "⏰ Deadline H-{$daysDiff}";
                $message = "Tugas '{$task->title}' akan jatuh tempo dalam {$daysDiff} hari lagi pada " . $dueDate->format('d M Y, H:i');
    
                $this->createNotificationIfNotExists($task, $type, $title, $message);
            }
    
            // ================================
            // Hari H (Deadline)
            // ================================
            if ($now->isSameDay($dueDate)) {
                $this->createNotificationIfNotExists(
                    $task,
                    'deadline_today',
                    '🚨 Deadline Hari Ini!',
                    "Tugas '{$task->title}' jatuh tempo hari ini pada " . $dueDate->format('H:i')
                );
            }
    
            // ================================
            // Setelah deadline (H+1 dan H+2)
            // ================================
            if ($daysDiff < 0 && $daysDiff >= -2) {
                $daysOverdue = abs($daysDiff);
                $this->createNotificationIfNotExists(
                    $task,
                    "overdue_h{$daysOverdue}",
                    "❌ Tugas Terlambat H+{$daysOverdue}",
                    "Tugas '{$task->title}' sudah terlambat {$daysOverdue} hari sejak " . $dueDate->format('d M Y, H:i')
                );
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
            Notification::create([
                'user_id' => $task->user_id,
                'task_id' => $task->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'created_at' => now(),
            ]);
        }
    }
    
    

    public static function createNewTaskNotification(Task $task)
    {
        $dueInfo = $task->due_date
            ? " dengan deadline " . Carbon::parse($task->due_date)->format('d M Y, H:i')
            : "";

        Notification::create([
            'user_id' => $task->user_id,
            'task_id' => $task->id,
            'type' => 'new_task',
            'title' => '✨ Tugas Baru Ditambahkan',
            'message' => "Tugas baru '{$task->title}' berhasil ditambahkan{$dueInfo}",
        ]);
    }

    public static function createTaskCompletedNotification(Task $task)
    {
        \App\Models\Notification::create([
            'user_id' => $task->user_id,
            'task_id' => $task->id,
            'type' => 'task_completed',
            'title' => '🎉 Tugas Selesai!',
            'message' => "Selamat! Tugas '{$task->title}' telah diselesaikan",
        ]);
    }

    
}
