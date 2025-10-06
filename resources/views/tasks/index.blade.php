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

/* Modal Styles */
.task-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 1000;
    backdrop-filter: blur(8px);
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 20px;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.4);
    width: 100%;
    max-width: 882px;
    max-height: 85vh;
    overflow-y: auto;
    animation: modalSlideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translate(-50%, -48%) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
}

.modal-header {
    padding: 28px 32px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px 20px 0 0;
    text-align: center;
    position: relative;
}

.modal-title {
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 8px 0;
    line-height: 1.3;
}

.modal-subtitle {
    font-size: 14px;
    opacity: 0.9;
    margin: 0;
    font-weight: 400;
}

.modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    padding: 8px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.modal-body {
    padding: 0;
}

/* Task Preview in Card */
.task-preview {
    padding: 20px 0 16px;
    border-bottom: 1px solid #f1f3f4;
}

.preview-content {
    padding: 0 32px;
}

.preview-description {
    color: #5f6368;
    line-height: 1.6;
    font-size: 15px;
    margin-bottom: 16px;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.show-more-btn {
    background: none;
    border: none;
    color: #667eea;
    font-weight: 600;
    cursor: pointer;
    padding: 0;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
}

.show-more-btn:hover {
    color: #5a6fd8;
    transform: translateX(2px);
}

/* Task Details in Modal */
.task-details {
    padding: 24px 32px 32px;
}

.detail-section {
    margin-bottom: 24px;
}

.detail-section:last-child {
    margin-bottom: 0;
}

.detail-title {
    font-size: 15px;
    font-weight: 600;
    color: #3c4043;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.detail-title i {
    color: #667eea;
    width: 16px;
}

.detail-content {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid #667eea;
}

.description-full {
    color: #5f6368;
    line-height: 1.7;
    font-size: 15px;
    white-space: pre-wrap;
    margin: 0;
}

/* Info Grid */
.info-grid {
    display: grid;
    gap: 16px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #e8eaed;
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #5f6368;
    font-size: 14px;
    font-weight: 500;
}

.info-label i {
    color: #667eea;
    width: 16px;
}

.info-value {
    font-weight: 600;
    color: #3c4043;
    font-size: 14px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fffaf0;
    color: #ed8936;
    border: 1px solid #fed7aa;
}

.status-done {
    background: #f0fff4;
    color: #38a169;
    border: 1px solid #9ae6b4;
}

/* Timeline Info */
.timeline-info {
    background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
    border-left: 4px solid #e53e3e;
}

.timeline-info.normal {
    background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
    border-left: 4px solid #38a169;
}

.timeline-info.urgent {
    background: linear-gradient(135deg, #fffaf0 0%, #fed7aa 100%);
    border-left: 4px solid #ed8936;
}

.timeline-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.timeline-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(229, 62, 62, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.timeline-icon.normal {
    background: rgba(56, 161, 105, 0.1);
}

.timeline-icon.urgent {
    background: rgba(237, 137, 54, 0.1);
}

.timeline-icon i {
    font-size: 18px;
    color: #e53e3e;
}

.timeline-icon.normal i {
    color: #38a169;
}

.timeline-icon.urgent i {
    color: #ed8936;
}

.timeline-text {
    flex: 1;
}

.timeline-title {
    font-weight: 600;
    color: #2d3748;
    font-size: 14px;
    margin: 0 0 4px 0;
}

.timeline-subtitle {
    color: #718096;
    font-size: 13px;
    margin: 0;
}

/* Modal Footer */
.modal-footer {
    padding: 20px 32px 28px;
    text-align: center;
    border-top: 1px solid #f1f3f4;
}

.btn-close-modal {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 32px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    font-size: 15px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-close-modal:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

/* Improved Task Card Description */
.task-description-compact {
    color: #5f6368;
    line-height: 1.5;
    font-size: 14px;
    margin-bottom: 12px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 4.5em;
}

.view-details-btn {
    background: none;
    border: none;
    color: #667eea;
    font-weight: 600;
    cursor: pointer;
    padding: 0;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.view-details-btn:hover {
    color: #5a6fd8;
    transform: translateX(2px);
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
    
    .modal-content {
        width: 95%;
        margin: 20px auto;
        max-width: 400px;
    }
    
    .modal-header {
        padding: 24px 20px 16px;
    }
    
    .modal-title {
        font-size: 20px;
    }
    
    .preview-content,
    .task-details {
        padding: 20px;
    }
    
    .modal-footer {
        padding: 16px 20px 24px;
    }
}

@media (max-width: 480px) {
    .modal-content {
        width: 98%;
        max-width: 360px;
    }
    
    .modal-header {
        padding: 20px 16px 14px;
    }
    
    .preview-content,
    .task-details {
        padding: 16px;
    }
    
    .detail-content {
        padding: 16px;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
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