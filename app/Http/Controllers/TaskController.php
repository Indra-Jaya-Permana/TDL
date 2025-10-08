<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
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
            $validated['due_date'] = Carbon::parse($request->due_date . ' ' . $request->due_time);
        } elseif ($request->due_date) {
            $validated['due_date'] = Carbon::parse($request->due_date)->endOfDay();
        }
    
        // Tambahkan user_id dari user yang sedang login
        $validated['user_id'] = Auth::id();
    
        // Simpan task ke database dan ambil hasilnya
        $task = Task::create($validated);
    
        // 🔔 Buat notifikasi tugas baru
        NotificationController::createNewTaskNotification($task);
    
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
                NotificationController::createTaskCompletedNotification($task);
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
                NotificationController::createTaskCompletedNotification($task);
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

    /**
     * Export tasks to Excel (CSV format)
     */
    public function exportToExcel()
    {
        $tasks = Task::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->get();

        $filename = 'daftar-tugas-' . date('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($tasks) {
            $file = fopen('php://output', 'w');
            
            // Add BOM untuk UTF-8 support di Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header kolom
            fputcsv($file, [
                'No',
                'Judul Tugas',
                'Deskripsi',
                'Status',
                'Deadline',
                'Dibuat Pada',
                'Diupdate Pada',
                'Keterangan Deadline'
            ]);

            // Data rows
            $no = 1;
            foreach ($tasks as $task) {
                $status = $task->status == 'done' ? 'Selesai' : 'Pending';
                $deadline = $task->due_date ? $task->due_date->format('d/m/Y H:i') : 'Tidak ada deadline';
                $created = $task->created_at->format('d/m/Y H:i');
                $updated = $task->updated_at->format('d/m/Y H:i');
                
                // Keterangan deadline
                $deadlineNote = 'Tidak ada deadline';
                if ($task->due_date) {
                    $now = now();
                    $diffInDays = $now->diffInDays($task->due_date, false);
                    
                    if ($task->status == 'done') {
                        $deadlineNote = 'Selesai';
                    } elseif ($diffInDays < 0) {
                        $deadlineNote = 'Terlambat ' . abs($diffInDays) . ' hari';
                    } elseif ($diffInDays <= 1) {
                        $deadlineNote = 'Deadline Dekat (Kurang dari 24 jam)';
                    } else {
                        $deadlineNote = $diffInDays . ' hari menuju deadline';
                    }
                }
                
                fputcsv($file, [
                    $no++,
                    $task->title,
                    $task->description ?? 'Tidak ada deskripsi',
                    $status,
                    $deadline,
                    $created,
                    $updated,
                    $deadlineNote
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export tasks to Google Sheets (TSV format)
     */
    public function exportToGoogleSheets()
    {
        $tasks = Task::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->get();

        // Generate TSV (Tab-Separated Values) untuk Google Sheets
        $filename = 'daftar-tugas-' . date('Y-m-d-His') . '.tsv';
        
        $headers = [
            'Content-Type' => 'text/tab-separated-values; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($tasks) {
            $file = fopen('php://output', 'w');
            
            // Add BOM untuk UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header kolom (Tab-separated)
            fwrite($file, implode("\t", [
                'No',
                'Judul Tugas',
                'Deskripsi',
                'Status',
                'Deadline',
                'Dibuat Pada',
                'Diupdate Pada',
                'Keterangan Deadline'
            ]) . "\n");

            // Data rows
            $no = 1;
            foreach ($tasks as $task) {
                $status = $task->status == 'done' ? 'Selesai' : 'Pending';
                $deadline = $task->due_date ? $task->due_date->format('d/m/Y H:i') : 'Tidak ada deadline';
                $created = $task->created_at->format('d/m/Y H:i');
                $updated = $task->updated_at->format('d/m/Y H:i');
                
                // Keterangan deadline
                $deadlineNote = 'Tidak ada deadline';
                if ($task->due_date) {
                    $now = now();
                    $diffInDays = $now->diffInDays($task->due_date, false);
                    
                    if ($task->status == 'done') {
                        $deadlineNote = 'Selesai';
                    } elseif ($diffInDays < 0) {
                        $deadlineNote = 'Terlambat ' . abs($diffInDays) . ' hari';
                    } elseif ($diffInDays <= 1) {
                        $deadlineNote = 'Deadline Dekat (Kurang dari 24 jam)';
                    } else {
                        $deadlineNote = $diffInDays . ' hari menuju deadline';
                    }
                }
                
                fwrite($file, implode("\t", [
                    $no++,
                    str_replace(["\t", "\n", "\r"], ' ', $task->title),
                    str_replace(["\t", "\n", "\r"], ' ', $task->description ?? 'Tidak ada deskripsi'),
                    $status,
                    $deadline,
                    $created,
                    $updated,
                    $deadlineNote
                ]) . "\n");
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}