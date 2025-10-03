<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Services\GmailService;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::orderBy('created_at', 'desc')->get();
        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        return view('tasks.create');
    }

    public function store(Request $request, GmailService $gmail)
    {
        $task = Task::create($request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]));

        $gmail->sendMail("Tugas baru ditambahkan: {$task->title}", "Detail: {$task->description}");

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil ditambahkan!');
    }

    public function update(Request $request, Task $task, GmailService $gmail)
    {
        $task->update(['status' => $request->status]);

        if ($request->status === 'done') {
            $gmail->sendMail("Tugas selesai: {$task->title}", "Detail: {$task->description}");
        }

        return back()->with('success', 'Status tugas diperbarui!');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return back()->with('success', 'Tugas dihapus!');
    }
}
