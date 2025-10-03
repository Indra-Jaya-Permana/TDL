@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Daftar Tugas</h2>
    <a href="{{ route('tasks.create') }}" class="btn btn-primary mb-3">Tambah Tugas</a>

    <table class="table">
        <thead>
            <tr>
                <th>Judul</th>
                <th>Status</th>
                <th>Deadline</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
            <tr>
                <td>{{ $task->title }}</td>
                <td>{{ ucfirst($task->status) }}</td>
                <td>{{ $task->due_date ?? '-' }}</td>
                <td>
                    <form action="{{ route('tasks.update', $task) }}" method="POST" class="d-inline">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="{{ $task->status == 'pending' ? 'done' : 'pending' }}">
                        <button class="btn btn-sm btn-success">{{ $task->status == 'pending' ? 'Selesai' : 'Undo' }}</button>
                    </form>
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
