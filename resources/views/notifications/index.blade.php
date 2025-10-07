@extends('layouts.app')

@section('title', 'Notifikasi')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
@endsection

@section('content')
<div class="notifications-page">

    <a href="{{ route('tasks.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i>
        Back to Tasks
    </a>
    <div class="notifications-header">
        <h1>Notifikasi</h1>
        <div class="header-actions">
            <form action="{{ route('notifications.read-all') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check-double"></i> Tandai Semua Terbaca
                </button>
            </form>
            {{-- Tombol Hapus Semua --}}
            <form action="{{ route('notifications.deleteAll') }}" method="POST"
                onsubmit="return confirm('Yakin ingin menghapus semua notifikasi?')"
                style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Hapus Semua
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
  
                    @php
                        $msg = $notification->message;

                        // Potong hanya bagian deskripsi
                        if (preg_match('/📝\s*Deskripsi:\s*(.+?)\s*🗓️/su', $msg, $matches)) {
                            $fullDesc = trim($matches[1]);
                            $shortDesc = \Illuminate\Support\Str::limit($fullDesc, 60, '...');
                            // Ganti hanya deskripsi panjang
                            $msg = preg_replace('/(📝\s*Deskripsi:\s*)(.+?)(\s*🗓️)/su', '$1' . $shortDesc . '$3', $msg);
                        }
                    @endphp
                    <p class="notif-message">{{ $msg }}</p>     
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