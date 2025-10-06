@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/task-list.css') }}">

<style>
/* Style khusus untuk tombol notifikasi */
.btn-notification {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    transition: all 0.3s ease;
}

.btn-notification:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    color: white;
}

.notification-bell {
    font-size: 18px;
    animation: bellRing 2s infinite;
}

@keyframes bellRing {
    0%, 100% { transform: rotate(0deg); }
    10%, 30% { transform: rotate(-10deg); }
    20%, 40% { transform: rotate(10deg); }
}

.notification-badge-count {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    min-width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    border: 3px solid white;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.header-buttons {
    display: flex;
    gap: 12px;
    align-items: center;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .header-buttons {
        width: 100%;
        flex-direction: column;
    }
    
    .btn-notification,
    .btn-add-task {
        width: 100%;
        justify-content: center;
    }
}
</style>

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
            <div class="header-buttons">
                {{-- Tombol Notifikasi dengan Badge --}}
                <a href="{{ route('notifications.index') }}" class="btn-notification" id="notificationBtn">
                    <i class="fas fa-bell notification-bell"></i>
                    <span>Notifikasi</span>
                    <span class="notification-badge-count" id="notificationBadge" style="display: none;">0</span>
                </a>

                {{-- Tombol Tambah Tugas --}}
                <a href="{{ route('tasks.create') }}" class="btn-add-task">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Tugas</span>
                </a>
            </div>
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
                        {{-- Tombol Edit --}}
                        <a href="{{ route('tasks.edit', $task) }}" class="btn-action btn-edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        
                        {{-- Tombol Toggle Status --}}
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
                        
                        {{-- Tombol Hapus --}}
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

{{-- Script untuk Update Badge Notifikasi Realtime --}}
<script>
    // Fungsi untuk update badge notifikasi
    function updateNotificationBadge() {
        fetch('{{ route("notifications.unread-count") }}')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('notificationBadge');
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(error => console.error('Error fetching notification count:', error));
    }

    // Update badge saat halaman load
    updateNotificationBadge();

    // Update badge setiap 30 detik
    setInterval(updateNotificationBadge, 30000);
</script>

<script src="{{ asset('js/task-list.js') }}"></script>
@endsection