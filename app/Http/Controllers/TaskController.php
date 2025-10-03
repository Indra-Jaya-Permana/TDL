<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
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

        // Hapus due_time karena tidak ada di database
        unset($validated['due_time']);

        Task::create($validated);

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil ditambahkan!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,done',
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index')->with('success', 'Status tugas berhasil diubah!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil dihapus!');
    }
}