@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="notifications-page">
    <div class="notifications-header">
        <h1><i class="fas fa-bell"></i> Notifikasi</h1>
        <div class="header-actions">
            <form action="{{ route('notifications.read-all') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check-double"></i> Tandai Semua Terbaca
                </button>
            </form>
            
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="notification-tabs">
        <button class="tab-btn active" data-filter="all">
            <i class="fas fa-inbox"></i> Semua
        </button>
        <button class="tab-btn" data-filter="unread">
            <i class="fas fa-envelope"></i> Belum Dibaca
        </button>
        <button class="tab-btn" data-filter="read">
            <i class="fas fa-envelope-open"></i> Sudah Dibaca
        </button>
    </div>

    {{-- Notification List --}}
    <div class="notifications-container">
        @forelse($notifications as $notification)
            <div class="notification-card {{ $notification->is_read ? 'read' : 'unread' }}" 
                 data-status="{{ $notification->is_read ? 'read' : 'unread' }}">
                
                {{-- Icon based on type --}}
                <div class="notif-icon {{ $notification->type }}">
                    @switch($notification->type)
                        @case('task_created')
                            <i class="fas fa-plus-circle"></i>
                            @break
                        @case('task_updated')
                            <i class="fas fa-edit"></i>
                            @break
                        @case('task_deadline_soon')
                            <i class="fas fa-clock"></i>
                            @break
                        @case('task_overdue')
                            <i class="fas fa-exclamation-triangle"></i>
                            @break
                        @case('task_completed')
                            <i class="fas fa-check-circle"></i>
                            @break
                        @default
                            <i class="fas fa-bell"></i>
                    @endswitch
                </div>

                {{-- Content --}}
                <div class="notif-content">
                    <div class="notif-header">
                        <h3 class="notif-title">{{ $notification->title }}</h3>
                        @if(!$notification->is_read)
                            <span class="unread-badge">Baru</span>
                        @endif
                    </div>
                    
                    <p class="notif-message">{{ $notification->message }}</p>
                    
                    <div class="notif-meta">
                        <span class="notif-time">
                            <i class="fas fa-clock"></i>
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                        
                        @if($notification->data && isset($notification->data['task_id']))
                            <a href="{{ route('tasks.show', $notification->data['task_id']) }}" 
                               class="btn-link">
                                <i class="fas fa-arrow-right"></i> Lihat Detail
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="notif-actions">
                    @if(!$notification->is_read)
                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-icon" title="Tandai Terbaca">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                    @endif
                    
                    <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-icon btn-danger" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                <h3>Belum Ada Notifikasi</h3>
                <p>Notifikasi akan muncul di sini ketika ada aktivitas baru</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
        <div class="pagination-wrapper">
            {{ $notifications->links() }}
        </div>
    @endif
</div>

<style>
.notifications-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

.notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.notifications-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
}

.header-actions {
    display: flex;
    gap: 10px;
}

/* Tabs */
.notification-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 2px solid #e5e7eb;
}

.tab-btn {
    padding: 12px 24px;
    background: none;
    border: none;
    color: #6b7280;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
}

.tab-btn:hover {
    color: #667eea;
}

.tab-btn.active {
    color: #667eea;
}

.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background: #667eea;
}

/* Notification Cards */
.notifications-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.notification-card {
    display: flex;
    gap: 20px;
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.2s;
}

.notification-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}

.notification-card.unread {
    border-left: 4px solid #667eea;
    background: #eff6ff;
}

.notif-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.notif-icon.task_created { background: #dbeafe; color: #2563eb; }
.notif-icon.task_updated { background: #fef3c7; color: #d97706; }
.notif-icon.task_deadline_soon { background: #fed7aa; color: #ea580c; }
.notif-icon.task_overdue { background: #fee2e2; color: #dc2626; }
.notif-icon.task_completed { background: #d1fae5; color: #059669; }

.notif-content {
    flex: 1;
}

.notif-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.notif-title {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.unread-badge {
    padding: 4px 12px;
    background: #667eea;
    color: white;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.notif-message {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 12px;
    line-height: 1.5;
}

.notif-meta {
    display: flex;
    gap: 20px;
    align-items: center;
}

.notif-time {
    font-size: 13px;
    color: #9ca3af;
}

.btn-link {
    color: #667eea;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}

.btn-link:hover {
    text-decoration: underline;
}

.notif-actions {
    display: flex;
    gap: 8px;
    align-items: flex-start;
}

.btn-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-icon:hover {
    background: #e5e7eb;
}

.btn-icon.btn-danger:hover {
    background: #fee2e2;
    color: #dc2626;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 64px;
    color: #d1d5db;
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: 20px;
    color: #6b7280;
    margin-bottom: 8px;
}

.empty-state p {
    color: #9ca3af;
}

/* Responsive */
@media (max-width: 768px) {
    .notifications-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .notification-card {
        flex-direction: column;
    }
    
    .notif-actions {
        justify-content: flex-end;
    }
}
</style>

<script>
// Tab filtering
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Update active state
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Filter notifications
        const filter = this.dataset.filter;
        document.querySelectorAll('.notification-card').forEach(card => {
            if (filter === 'all') {
                card.style.display = 'flex';
            } else {
                card.style.display = card.dataset.status === filter ? 'flex' : 'none';
            }
        });
    });
});
</script>
@endsection