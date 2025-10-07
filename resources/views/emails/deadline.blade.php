<!-- <x-mail::message>
<div style="text-align: center; margin-bottom: 25px; border-bottom: 2px solid #e74c3c; padding-bottom: 15px;">
    <h1 style="color: #e74c3c; font-size: 26px; margin: 0;">
        @if($daysLeft > 0)
            ⏰ Deadline H-{{ $daysLeft }}
        @else
            🚨 Deadline Hari Ini
        @endif
    </h1>
    <p style="color: #7f8c8d; margin-top: 8px; font-size: 14px;">Pemberitahuan Tenggat Waktu</p>
</div>

<div style="background-color: #f9fafb; border-radius: 12px; padding: 24px; border: 1px solid #ecf0f1;">
    <p style="color: #2c3e50; line-height: 1.6; font-size: 15px;">
        Halo <strong>{{ $name }}</strong>, 👋<br><br>
        Ini pengingat bahwa tugas kamu 
        <strong style="color: #e74c3c;">"{{ $taskTitle }}"</strong> 
        akan jatuh tempo 
        <strong>
            {{ $daysLeft > 0 ? 'dalam ' . $daysLeft . ' hari lagi' : 'hari ini' }}
        </strong>, pada 
        <strong>{{ $dueDate }}</strong>.
    </p>

    <div style="margin-top: 18px; background: #fff; padding: 15px 18px; border-radius: 8px; border-left: 4px solid #e74c3c;">
        <p style="margin: 0; color: #7f8c8d; font-size: 14px;">
            💡 <em>Segera selesaikan sebelum waktu habis agar tidak terlambat!</em>
        </p>
    </div>
</div>

<x-mail::button :url="$taskUrl" color="error">
    📋 Lihat Detail Tugas
</x-mail::button>

<div style="text-align: center; color: #95a5a6; font-size: 12px; margin-top: 30px; border-top: 1px solid #ecf0f1; padding-top: 20px;">
    <p style="margin: 5px 0;">
        <strong>Terima kasih,</strong><br>
        <span style="color: #2c3e50;">Tim {{ config('app.name') }}</span>
    </p>
    <p style="margin: 5px 0; font-size: 11px;">
        Email ini dikirim otomatis. Mohon jangan membalas email ini.
    </p>
</div>
</x-mail::message> -->
