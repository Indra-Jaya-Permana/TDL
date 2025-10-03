@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/task-list.css') }}">

<div class="task-list-container">
    <div class="task-list-wrapper">
        {{-- Header Section --}}
        <div class="page-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div>
                    <h2 class="page-title">Daftar Tugas</h2>
                    <p class="page-subtitle">Kelola semua tugas Anda di sini</p>
                </div>
            </div>
            <a href="{{ route('tasks.create') }}" class="btn-add-task">
                <i class="fas fa-plus"></i>
                <span>Tambah Tugas</span>
            </a>
        </div>

        {{-- Success Alert --}}
        @if(session('success'))
            <div class="alert-custom alert-success-custom">
                <div class="alert-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="alert-content">
                    {{ session('success') }}
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        {{-- Task Cards --}}
        <div class="tasks-grid">
            @forelse($tasks as $task)
                <div class="task-card {{ $task->status == 'done' ? 'task-done' : '' }}">
                    {{-- Task Header --}}
                    <div class="task-header">
                        <div class="task-status">
                            @if($task->status == 'done')
                                <span class="status-badge status-success">
                                    <i class="fas fa-check"></i> Selesai
                                </span>
                            @else
                                <span class="status-badge status-warning">
                                    <i class="fas fa-clock"></i> Pending
                                </span>
                            @endif
                        </div>
                        
                        @if($task->due_date)
                            @php
                                $now = now();
                                $diffInHours = $now->diffInHours($task->due_date, false);
                            @endphp
                            
                            @if($task->status == 'done')
                                <span class="deadline-badge badge-done">
                                    <i class="fas fa-check-circle"></i> Selesai
                                </span>
                            @elseif($diffInHours < 0)
                                <span class="deadline-badge badge-danger">
                                    <i class="fas fa-exclamation-triangle"></i> Terlambat {{ abs(round($diffInHours / 24)) }} hari
                                </span>
                            @elseif($diffInHours <= 24)
                                <span class="deadline-badge badge-urgent">
                                    <i class="fas fa-fire"></i> Kurang dari 24 jam!
                                </span>
                            @elseif($diffInHours <= 72)
                                <span class="deadline-badge badge-info">
                                    <i class="fas fa-calendar-day"></i> {{ round($diffInHours / 24) }} hari lagi
                                </span>
                            @else
                                <span class="deadline-badge badge-normal">
                                    <i class="fas fa-calendar-alt"></i> {{ round($diffInHours / 24) }} hari lagi
                                </span>
                            @endif
                        @endif
                    </div>

                    {{-- Task Content --}}
                    <div class="task-content">
                        <h3 class="task-title">{{ $task->title }}</h3>
                        @if($task->description)
                            <p class="task-description">{{ Str::limit($task->description, 80) }}</p>
                        @endif
                        
                        @if($task->due_date)
                            <div class="task-deadline">
                                <i class="fas fa-calendar"></i>
                                <span>{{ $task->due_date->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Task Actions --}}
                    <div class="task-actions">
                        <form action="{{ route('tasks.update', $task) }}" method="POST" class="action-form">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="{{ $task->status == 'pending' ? 'done' : 'pending' }}">
                            <button class="btn-action btn-complete" type="submit">
                                @if($task->status == 'pending')
                                    <i class="fas fa-check"></i> Selesai
                                @else
                                    <i class="fas fa-undo"></i> Undo
                                @endif
                            </button>
                        </form>
                        
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="action-form" onsubmit="return confirm('Yakin ingin menghapus tugas ini?')">
                            @csrf @method('DELETE')
                            <button class="btn-action btn-delete" type="submit">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3 class="empty-title">Belum Ada Tugas</h3>
                    <p class="empty-text">Mulai tambahkan tugas baru untuk mengelola pekerjaan Anda!</p>
                    <a href="{{ route('tasks.create') }}" class="btn-empty-action">
                        <i class="fas fa-plus"></i> Tambah Tugas Pertama
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script src="{{ asset('js/task-list.js') }}"></script>
@endsection