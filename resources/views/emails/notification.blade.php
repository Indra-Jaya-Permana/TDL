<x-mail::message>
<div style="text-align: center; margin-bottom: 25px; border-bottom: 2px solid #3498db; padding-bottom: 15px;">
    <h1 style="color: #3498db; font-size: 26px; margin: 0;">
        @if($type === 'new_task')
            🆕 Tugas Baru
        @elseif($type === 'task_completed')
            ✅ Tugas Selesai
        @elseif(str_contains($type, 'deadline'))
            ⏰ Deadline
        @elseif(str_contains($type, 'overdue'))
            ❌ Tugas Terlambat
        @else
            📢 Notifikasi
        @endif
    </h1>
    <p style="color: #7f8c8d; margin-top: 8px; font-size: 14px;">Pemberitahuan Sistem</p>
</div>

{{-- ✨ Tampilan modern hanya untuk isi pesan --}}
<div style="
    background: #ffffff;
    border: 1px solid #e6e9ef;
    border-radius: 14px;
    padding: 22px 26px;
    color: #2c3e50;
    font-family: 'Segoe UI', Roboto, Arial, sans-serif;
    line-height: 1.8;
    font-size: 15.5px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
">
    <div style="
        border-left: 4px solid #3498db;
        padding-left: 14px;
        margin-bottom: 10px;
        font-weight: 600;
        color: #34495e;
        font-size: 16px;
    ">
        📘 Detail Tugas
    </div>
        {!! nl2br(e($messageBody)) !!}
</div>





@if(str_contains($type, 'deadline') || str_contains($type, 'overdue') || $type === 'new_task')
<x-mail::button :url="route('tasks.index')" color="primary">
    🔗 Buka Tugas Saya
</x-mail::button>
@endif

<div style="text-align: center; color: #95a5a6; font-size: 12px; margin-top: 30px; border-top: 1px solid #ecf0f1; padding-top: 20px;">
    <p style="margin: 5px 0;">
        <strong>Terima kasih,</strong><br>
        <span style="color: #2c3e50;">Tim Developer</span>
    </p>
    <p style="margin: 5px 0; font-size: 11px;">
        Kelola notifikasi Anda di 
        <a href="{{ route('notifications.index') }}" style="color: #3498db; text-decoration: none;">Pengaturan Notifikasi</a>.
    </p>
</div>
</x-mail::message>
