<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::orderBy('created_at', 'desc')->get();
        return view('tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tasks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
        ]);
    
        // Gabungkan tanggal dan waktu
        if ($request->due_date && $request->due_time) {
            $validated['due_date'] = \Carbon\Carbon::parse($request->due_date . ' ' . $request->due_time);
        } elseif ($request->due_date) {
            $validated['due_date'] = \Carbon\Carbon::parse($request->due_date)->endOfDay();
        }
    
        // Tambahkan user_id dari user yang sedang login
        $validated['user_id'] = \Illuminate\Support\Facades\Auth::id();
    
        // Simpan task ke database dan ambil hasilnya
        $task = \App\Models\Task::create($validated);
    
        // 🔔 Buat notifikasi tugas baru (jika kamu ingin notifikasi otomatis)
        \App\Http\Controllers\NotificationController::createNewTaskNotification($task);
    
        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil ditambahkan!');
    }
    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        return view('tasks.edit', compact('task'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        // Simpan status lama sebelum update
        $oldStatus = $task->status;
    
        // Cek apakah update status saja (misalnya dari tombol toggle)
        if ($request->has('status') && !$request->has('title')) {
            $validated = $request->validate([
                'status' => 'required|in:pending,done',
            ]);
    
            $task->update($validated);
    
            // 🔔 Buat notifikasi jika status berubah jadi "done"
            if ($oldStatus === 'pending' && $validated['status'] === 'done') {
                \App\Http\Controllers\NotificationController::createTaskCompletedNotification($task);
            }
    
            return redirect()->route('tasks.index')->with('success', 'Status tugas berhasil diubah!');
        } else {
            // Update data lengkap (dari form edit)
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'due_date' => 'nullable|date',
                'due_time' => 'nullable|date_format:H:i',
                'status' => 'required|in:pending,done',
            ]);
    
            // Gabungkan tanggal dan waktu
            if ($request->due_date && $request->due_time) {
                $validated['due_date'] = Carbon::parse($request->due_date . ' ' . $request->due_time);
            } elseif ($request->due_date) {
                $validated['due_date'] = Carbon::parse($request->due_date)->endOfDay();
            } else {
                $validated['due_date'] = null;
            }
    
            // Hapus due_time karena tidak ada di database
            unset($validated['due_time']);
    
            $task->update($validated);
    
            // 🔔 Buat notifikasi jika status berubah jadi "done"
            if ($oldStatus === 'pending' && $validated['status'] === 'done') {
                \App\Http\Controllers\NotificationController::createTaskCompletedNotification($task);
            }
    
            return redirect()->route('tasks.index')->with('success', 'Tugas berhasil diperbarui!');
        }
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        \Log::info('DEBUG: Memulai proses hapus task ' . $task->title);
    
        // 🔔 Coba buat notifikasi
        try {
            \App\Models\Notification::create([
                'user_id' => $task->user_id,
                'task_id' => $task->id,
                'type' => 'task_deleted',
                'title' => '🗑️ Tugas Dihapus!',
                'message' => "Tugas '{$task->title}' telah dihapus dari daftar tugas.",
            ]);
    
            \Log::info('DEBUG: Notifikasi berhasil dibuat');
        } catch (\Exception $e) {
            \Log::error('GAGAL buat notifikasi: ' . $e->getMessage());
        }
    
        // 🗑️ Hapus task
        $task->delete();
    
        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil dihapus!');
    }
    
    
}