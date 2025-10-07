@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/task-list.css') }}">
<link rel="stylesheet" href="{{ asset('css/task-list-custom.css') }}">

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
                                    <i class="fas fa-fire"></i> Deadline Dekat
                                </span>
                            @elseif($diffInHours <= 72)
                                <span class="deadline-badge badge-info">
                                    <i class="fas fa-calendar-day"></i> {{ round($diffInHours / 24) }} hari
                                </span>
                            @else
                                <span class="deadline-badge badge-normal">
                                    <i class="fas fa-calendar-alt"></i> {{ round($diffInHours / 24) }} hari
                                </span>
                            @endif
                        @endif
                    </div>

                    {{-- Task Content --}}
                    <div class="task-content">
                        <h3 class="task-title">{{ $task->title }}</h3>
                        
                        {{-- Deskripsi dengan preview yang lebih rapi --}}
                        @if($task->description)
                            <div class="task-preview">
                                <div class="preview-content">
                                    <p class="preview-description">
                                        {{ Str::limit($task->description, 150) }}
                                    </p>
                                    <button class="show-more-btn" onclick="showTaskDetails({{ $task->id }})">
                                        <i class="fas fa-expand-alt"></i> Lihat Detail Lengkap
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="task-preview">
                                <div class="preview-content">
                                    <p class="preview-description text-muted">
                                        <i>Tidak ada deskripsi untuk tugas ini.</i>
                                    </p>
                                    <button class="show-more-btn" onclick="showTaskDetails({{ $task->id }})">
                                        <i class="fas fa-expand-alt"></i> Lihat Detail Tugas
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Deadline --}}
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
                                    <i class="fas fa-undo"></i> Batal
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

{{-- Modal untuk Detail Tugas --}}
<div id="taskModal" class="task-modal">
    <div class="modal-content">
        {{-- Modal Header --}}
        <div class="modal-header">
            <button class="modal-close" onclick="closeTaskModal()">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="modal-title" id="modalTaskTitle"></h3>
        </div>

        {{-- Modal Body --}}
        <div class="modal-body">
            {{-- Task Details --}}
            <div class="task-details">
                {{-- Deskripsi --}}
                <div class="detail-section">
                    <h4 class="detail-title">
                        <i class="fas fa-align-left"></i> Deskripsi Tugas
                    </h4>
                    <div class="detail-content">
                        <p id="modalTaskDescription" class="description-full"></p>
                    </div>
                </div>

                {{-- Informasi Tugas --}}
                <div class="detail-section">
                    <h4 class="detail-title">
                        <i class="fas fa-info-circle"></i> Informasi Tugas
                    </h4>
                    <div class="detail-content">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">
                                    <i class="fas fa-tag"></i> Status
                                </span>
                                <span id="modalTaskStatus" class="status-badge"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">
                                    <i class="fas fa-calendar-plus"></i> Dibuat
                                </span>
                                <span id="modalTaskCreated" class="info-value"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">
                                    <i class="fas fa-calendar-check"></i> Diupdate
                                </span>
                                <span id="modalTaskUpdated" class="info-value"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">
                                    <i class="fas fa-clock"></i> Deadline
                                </span>
                                <span id="modalTaskDeadline" class="info-value"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Timeline Status --}}
                <div class="detail-section">
                    <h4 class="detail-title">
                        <i class="fas fa-history"></i> Status Timeline
                    </h4>
                    <div id="modalTaskTimeline" class="detail-content timeline-info">
                        {{-- Timeline content akan diisi oleh JavaScript --}}
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="modal-footer">
            <button class="btn-close-modal" onclick="closeTaskModal()">
             Tutup Detail
            </button>
        </div>
    </div>
</div>

<script>
// Data tasks untuk modal
const tasksData = {
    @foreach($tasks as $task)
        {{ $task->id }}: {
            title: `{{ $task->title }}`,
            description: `{{ addslashes($task->description) }}`,
            status: `{{ $task->status }}`,
            status_text: `{{ $task->status == 'done' ? 'Selesai' : 'Pending' }}`,
            created_at: `{{ $task->created_at->format('d M Y, H:i') }}`,
            updated_at: `{{ $task->updated_at->format('d M Y, H:i') }}`,
            due_date: `{{ $task->due_date ? $task->due_date->format('d M Y, H:i') : 'Tidak ada deadline' }}`,
            @if($task->due_date)
                @php
                    $now = now();
                    $dueDate = $task->due_date;
                    
                    // Hitung selisih hari berdasarkan tanggal (bukan jam)
                    $start = $now->copy()->startOfDay();
                    $end = $dueDate->copy()->startOfDay();
                    $diffInDays = $start->diffInDays($end, false); // false untuk tidak mengabsolutkan
                    
                    // Tentukan status timeline
                    $isOverdue = $diffInDays < 0;
                    $isUrgent = $diffInDays >= 0 && $diffInDays <= 1; // Hari ini atau besok
                    $isNormal = $diffInDays > 1;
                    $daysLeft = abs($diffInDays);
                @endphp
                timeline: {
                    is_overdue: {{ $isOverdue ? 'true' : 'false' }},
                    is_urgent: {{ $isUrgent ? 'true' : 'false' }},
                    is_normal: {{ $isNormal ? 'true' : 'false' }},
                    days_left: {{ $daysLeft }},
                    diff_days: {{ $diffInDays }}
                }
            @else
                timeline: null
            @endif
        },
    @endforeach
};

// Fungsi untuk menampilkan modal detail tugas
function showTaskDetails(taskId) {
    const task = tasksData[taskId];
    if (!task) return;

    // Set modal content
    document.getElementById('modalTaskTitle').textContent = task.title;
    
    // Set deskripsi
    const descriptionElement = document.getElementById('modalTaskDescription');
    descriptionElement.textContent = task.description || 'Tidak ada deskripsi untuk tugas ini.';
    
    // Set status
    const statusElement = document.getElementById('modalTaskStatus');
    statusElement.textContent = task.status_text;
    statusElement.className = `status-badge ${task.status === 'done' ? 'status-done' : 'status-pending'}`;
    
    // Set dates
    document.getElementById('modalTaskCreated').textContent = task.created_at;
    document.getElementById('modalTaskUpdated').textContent = task.updated_at;
    document.getElementById('modalTaskDeadline').textContent = task.due_date;
    
    // Set timeline
    const timelineElement = document.getElementById('modalTaskTimeline');
    let timelineHTML = '';
    
    if (task.timeline) {
        if (task.timeline.is_overdue) {
            timelineHTML = `
                <div class="timeline-content">
                    <div class="timeline-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="timeline-text">
                        <div class="timeline-title">Tugas Terlambat</div>
                        <div class="timeline-subtitle">Sudah melewati deadline ${task.timeline.days_left} hari</div>
                    </div>
                </div>
            `;
            timelineElement.className = 'detail-content timeline-info';
        } else if (task.timeline.is_urgent) {
            timelineHTML = `
                <div class="timeline-content">
                    <div class="timeline-icon urgent">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div class="timeline-text">
                        <div class="timeline-title">Deadline Mendekat</div>
                        <div class="timeline-subtitle">Kurang dari 24 jam lagi</div>
                    </div>
                </div>
            `;
            timelineElement.className = 'detail-content timeline-info urgent';
        } else if (task.timeline.is_normal) {
            timelineHTML = `
                <div class="timeline-content">
                    <div class="timeline-icon normal">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="timeline-text">
                        <div class="timeline-title">Masih Ada Waktu</div>
                        <div class="timeline-subtitle">${task.timeline.days_left} hari menuju deadline</div>
                    </div>
                </div>
            `;
            timelineElement.className = 'detail-content timeline-info normal';
        }
    } else {
        timelineHTML = `
            <div class="timeline-content">
                <div class="timeline-icon normal">
                    <i class="fas fa-infinity"></i>
                </div>
                <div class="timeline-text">
                    <div class="timeline-title">Tidak Ada Deadline</div>
                    <div class="timeline-subtitle">Tugas ini tidak memiliki batas waktu</div>
                </div>
            </div>
        `;
        timelineElement.className = 'detail-content timeline-info normal';
    }
    
    timelineElement.innerHTML = timelineHTML;
    
    // Show modal
    document.getElementById('taskModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

// Fungsi untuk menutup modal
function closeTaskModal() {
    document.getElementById('taskModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal ketika klik di luar content
document.getElementById('taskModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTaskModal();
    }
});

// Close modal dengan ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeTaskModal();
    }
});

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