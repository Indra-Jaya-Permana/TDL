<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index()
    {
        // Generate notifikasi baru sebelum menampilkan
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

    /**
     * API endpoint untuk dropdown notification
     * Mengembalikan data dalam format JSON
     */
    public function api()
    {
        // Generate notifikasi terbaru
        $this->generateNotifications();

        $notifications = Notification::forUser(Auth::id())
            ->with('task')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function($notif) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->title,
                    'message' => $notif->message,
                    'type' => $notif->type,
                    'is_read' => $notif->is_read,
                    'time_ago' => $notif->created_at->diffForHumans(),
                    'url' => $notif->task_id 
                        ? route('tasks.show', $notif->task_id) 
                        : null,
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Notification::forUser(Auth::id())->unread()->count()
        ]);
    }

    /**
     * Get unread notifications count (untuk badge di navbar)
     */
    public function unreadCount()
    {
        $this->generateNotifications();
        
        $count = Notification::forUser(Auth::id())
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mark single notification as read (untuk dropdown)
     */
    public function markAsRead($id)
    {
        $notification = Notification::forUser(Auth::id())
            ->findOrFail($id);

        $notification->markAsRead();

        // Jika request dari AJAX, return JSON
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ditandai sudah dibaca'
            ]);
        }

        // Jika request biasa, redirect back
        return redirect()->back()->with('success', 'Notifikasi ditandai sudah dibaca');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Notification::forUser(Auth::id())
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return redirect()->back()->with('success', 'Semua notifikasi ditandai sudah dibaca');
    }

    /**
     * Delete a notification
     */
    public function destroy(Notification $notification)
    {
        // Pastikan notifikasi milik user yang login
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $notification->delete();

        return redirect()->back()->with('success', 'Notifikasi berhasil dihapus');
    }

    /**
     * Delete all notifications (read & unread)
     */
    public function deleteAll()
    {
        Notification::forUser(Auth::id())->delete();

        return redirect()->back()->with('success', 'Semua notifikasi berhasil dihapus');
    }

    /**
     * Delete all read notifications only
     */
    public function deleteAllRead()
    {
        Notification::forUser(Auth::id())
            ->where('is_read', true)
            ->delete();

        return redirect()->back()->with('success', 'Semua notifikasi yang sudah dibaca berhasil dihapus');
    }

    /**
     * Generate notifications based on task deadlines
     * DIPANGGIL OTOMATIS setiap kali halaman notifikasi dibuka
     * TANPA PERLU php artisan schedule:work
     */
    public function generateNotifications()
    {
        $now = Carbon::now();
        $userId = Auth::id();

        // Ambil semua task user yang statusnya pending dan ada deadline
        $tasks = Task::where('user_id', $userId)
            ->where('status', 'pending')
            ->whereNotNull('due_date')
            ->get();

        foreach ($tasks as $task) {
            $dueDate = Carbon::parse($task->due_date);
            
            // H-3: 3 hari sebelum deadline
            if ($now->between($dueDate->copy()->subDays(3)->startOfDay(), $dueDate->copy()->subDays(2)->endOfDay())) {
                $this->createNotificationIfNotExists(
                    $task, 
                    'deadline_h3', 
                    '⏰ Deadline H-3', 
                    "Tugas '{$task->title}' akan jatuh tempo 3 hari lagi pada " . $dueDate->format('d M Y, H:i')
                );
            }

            // H-1: 1 hari sebelum deadline
            if ($now->between($dueDate->copy()->subDay()->startOfDay(), $dueDate->copy()->startOfDay()->subSecond())) {
                $this->createNotificationIfNotExists(
                    $task, 
                    'deadline_h1', 
                    '🔔 Deadline H-1', 
                    "Tugas '{$task->title}' akan jatuh tempo besok pada " . $dueDate->format('d M Y, H:i')
                );
            }

            // Deadline hari ini (berdasarkan jam)
            if ($now->isSameDay($dueDate) && $now->isBefore($dueDate)) {
                $hoursLeft = $now->diffInHours($dueDate, false);
                
                // Notifikasi jika tersisa 3 jam atau kurang
                if ($hoursLeft >= 0 && $hoursLeft <= 3) {
                    $minutesLeft = $now->diffInMinutes($dueDate, false);
                    
                    if ($minutesLeft < 60) {
                        $timeText = $minutesLeft . ' menit';
                    } else {
                        $timeText = $hoursLeft . ' jam';
                    }
                    
                    $this->createNotificationIfNotExists(
                        $task, 
                        'deadline_today', 
                        '🚨 Deadline Hari Ini!', 
                        "URGENT! Tugas '{$task->title}' akan jatuh tempo dalam {$timeText} lagi!"
                    );
                }
            }

            // Overdue: Sudah lewat deadline
            if ($now->isAfter($dueDate)) {
                $daysOverdue = $now->diffInDays($dueDate);
                
                // H+1 sampai H+4 (1-4 hari overdue)
                if ($daysOverdue >= 1 && $daysOverdue <= 4) {
                    $this->createNotificationIfNotExists(
                        $task, 
                        'overdue_h1', 
                        '❌ Tugas Terlewat!', 
                        "Waduh! Tugas '{$task->title}' sudah terlewat {$daysOverdue} hari nih sejak " . $dueDate->format('d M Y, H:i')
                    );
                }

                // H+5 atau lebih (5+ hari overdue)
                if ($daysOverdue >= 5) {
                    $this->createNotificationIfNotExists(
                        $task, 
                        'overdue_h5', 
                        '⚠️ Tugas Sangat Terlewat!', 
                        "Waduh banget! Tugas '{$task->title}' sudah terlewat {$daysOverdue} hari! Segera selesaikan!"
                    );
                }
            }
        }
    }

    /**
     * Create notification if not exists for today
     * Cek duplikasi berdasarkan task_id, type, dan tanggal hari ini
     */
    private function createNotificationIfNotExists($task, $type, $title, $message)
    {
        $today = Carbon::today();
        
        // Cek apakah notifikasi dengan type ini untuk task ini sudah ada hari ini
        $exists = Notification::where('task_id', $task->id)
            ->where('user_id', $task->user_id)
            ->where('type', $type)
            ->whereDate('created_at', $today)
            ->exists();

        // Kalau belum ada, buat notifikasi baru
        if (!$exists) {
            Notification::create([
                'user_id' => $task->user_id,
                'task_id' => $task->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
            ]);
        }
    }

    /**
     * Create notification for new task
     * Dipanggil dari TaskController saat task baru dibuat
     */
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

    /**
     * Create notification for task update
     * Dipanggil dari TaskController saat task diupdate
     */
    public static function createTaskUpdateNotification(Task $task, $changes = [])
    {
        $changeText = '';
        
        if (!empty($changes)) {
            $changesList = [];
            if (isset($changes['status'])) {
                $changesList[] = "status menjadi " . strtoupper($changes['status']);
            }
            if (isset($changes['priority'])) {
                $changesList[] = "prioritas menjadi " . strtoupper($changes['priority']);
            }
            if (isset($changes['due_date'])) {
                $changesList[] = "deadline menjadi " . Carbon::parse($changes['due_date'])->format('d M Y, H:i');
            }
            
            if (!empty($changesList)) {
                $changeText = " (" . implode(', ', $changesList) . ")";
            }
        }

        Notification::create([
            'user_id' => $task->user_id,
            'task_id' => $task->id,
            'type' => 'task_updated',
            'title' => '📝 Tugas Diperbarui',
            'message' => "Tugas '{$task->title}' telah diperbarui{$changeText}",
        ]);
    }

    /**
     * Create notification for task completion
     * Dipanggil dari TaskController saat task diselesaikan
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
}