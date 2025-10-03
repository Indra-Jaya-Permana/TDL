@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Daftar Tugas</h2>
    <a href="{{ route('tasks.create') }}" class="btn btn-primary mb-3">Tambah Tugas</a>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Judul</th>
                <th>Status</th>
                <th>Deadline</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
            <tr class="{{ $task->status == 'done' ? 'table-success' : '' }}">
                <td>
                    <strong>{{ $task->title }}</strong>
                    @if($task->description)
                        <br><small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                    @endif
                </td>
                <td>
                    @if($task->status == 'done')
                        <span class="badge bg-success">Selesai</span>
                    @else
                        <span class="badge bg-warning text-dark">Pending</span>
                    @endif
                </td>
                <td>
                    @if($task->due_date)
                        <div>
                            {{ $task->due_date->format('d/m/Y H:i') }}
                        </div>
                        
                        {{-- Status Deadline --}}
                        @php
                            $now = now();
                            $diffInHours = $now->diffInHours($task->due_date, false);
                        @endphp
                        
                        @if($task->status == 'done')
                            <span class="badge bg-success">✓ Selesai</span>
                        @elseif($diffInHours < 0)
                            <span class="badge bg-danger">⚠ Terlambat {{ abs(round($diffInHours / 24)) }} hari</span>
                        @elseif($diffInHours <= 24)
                            <span class="badge bg-warning text-dark">🔥 Kurang dari 24 jam!</span>
                        @elseif($diffInHours <= 72)
                            <span class="badge bg-info">📅 {{ round($diffInHours / 24) }} hari lagi</span>
                        @else
                            <span class="badge bg-secondary">{{ round($diffInHours / 24) }} hari lagi</span>
                        @endif
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    <form action="{{ route('tasks.update', $task) }}" method="POST" class="d-inline">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="{{ $task->status == 'pending' ? 'done' : 'pending' }}">
                        <button class="btn btn-sm btn-success" type="submit">
                            {{ $task->status == 'pending' ? 'Selesai' : 'Undo' }}
                        </button>
                    </form>
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus tugas ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center text-muted">Belum ada tugas. Tambahkan tugas baru!</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection