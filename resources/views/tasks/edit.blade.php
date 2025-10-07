@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/edit.css') }}">
@endsection

@section('content')

            <a href="{{ route('tasks.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Back to Tasks
            </a>


        {{-- Header --}}
        <div class="card-header-custom">
            <div class="header-icon">
                <i class="fas fa-edit"></i>
            </div>
            <h2 class="card-title">Edit Tugas</h2>
            <p class="card-subtitle">Perbarui informasi tugas Anda</p>
        </div>

        {{-- Pesan error validasi --}}
        @if ($errors->any())
            <div class="alert-custom alert-danger-custom">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="alert-content">
                    <strong>Terjadi kesalahan!</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Form Edit Tugas --}}
        <form action="{{ route('tasks.update', $task) }}" method="POST" class="task-form">
            @csrf
            @method('PUT')

            <div class="form-group-custom">
                <label class="form-label-custom">
                    <i class="fas fa-heading"></i>
                    Judul Tugas
                </label>
                <input type="text" name="title" class="form-input-custom" 
                       value="{{ old('title', $task->title) }}" 
                       placeholder="Masukkan judul tugas..." required>
            </div>

            <div class="form-group-custom">
                <label class="form-label-custom">
                    <i class="fas fa-align-left"></i>
                    Deskripsi
                </label>
                <textarea name="description" class="form-textarea-custom" rows="4" 
                          placeholder="Jelaskan detail tugas...">{{ old('description', $task->description) }}</textarea>
            </div>

            <div class="form-row-custom">
                <div class="form-group-custom">
                    <label class="form-label-custom">
                        <i class="fas fa-calendar-alt"></i>
                        Tanggal Deadline
                    </label>
                    <input type="date" name="due_date" class="form-input-custom" 
                           value="{{ old('due_date', $task->due_date ? $task->due_date->format('Y-m-d') : '') }}" 
                           id="due_date">
                </div>
                <div class="form-group-custom">
                    <label class="form-label-custom">
                        <i class="fas fa-clock"></i>
                        Jam Deadline
                    </label>
                    <input type="time" name="due_time" class="form-input-custom" 
                           value="{{ old('due_time', $task->due_date ? $task->due_date->format('H:i') : '') }}" 
                           id="due_time">
                    <small class="form-hint">Opsional. Jika tidak diisi, deadline akan diatur ke akhir hari (23:59)</small>
                </div>
            </div>

            <div class="form-group-custom">
                <label class="form-label-custom">
                    <i class="fas fa-tasks"></i>
                    Status
                </label>
                <select name="status" class="form-input-custom">
                    <option value="pending" {{ old('status', $task->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="done" {{ old('status', $task->status) == 'done' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Update Tugas
                </button>
                <a href="{{ route('tasks.index') }}" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Batal
                </a>
            </div>
        </form>
<script>
// Auto-enable time input ketika tanggal dipilih
document.getElementById('due_date').addEventListener('change', function() {
    const timeInput = document.getElementById('due_time');
    if (this.value && !timeInput.value) {
        timeInput.focus();
    }
});
</script>
@endsection