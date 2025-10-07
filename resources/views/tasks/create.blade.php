@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/create-task.css') }}">

            <a href="{{ route('tasks.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Back to Tasks
            </a>
        <div class="card-header-custom">
            <div class="header-icon">
                <i class="fas fa-plus-circle"></i>
            </div>
            <h2 class="card-title">Tambah Tugas Baru</h2>
            <p class="card-subtitle">Isi form di bawah untuk membuat tugas baru</p>
        </div>

        {{-- Pesan error validasi --}}
        @if ($errors->any())
            <div class="alert-custom alert-danger-custom">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="alert-content">
                    <strong>Terjadi kesalahan!</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Form Tambah Tugas --}}
        <form action="{{ route('tasks.store') }}" method="POST" class="task-form">
            @csrf

            <div class="form-group-custom">
                <label class="form-label-custom">
                    <i class="fas fa-heading"></i>
                    Judul Tugas
                </label>
                <input type="text" name="title" class="form-input-custom" 
                       value="{{ old('title') }}" 
                       placeholder="Masukkan judul tugas..."
                       required>
            </div>

            <div class="form-group-custom">
                <label class="form-label-custom">
                    <i class="fas fa-align-left"></i>
                    Deskripsi
                </label>
                <textarea name="description" class="form-textarea-custom" 
                          rows="4" 
                          placeholder="Masukkan deskripsi tugas (opsional)...">{{ old('description') }}</textarea>
            </div>

            <div class="form-row-custom">
                <div class="form-group-custom">
                    <label class="form-label-custom">
                        <i class="fas fa-calendar-alt"></i>
                        Tanggal Deadline
                    </label>
                    <input type="date" name="due_date" class="form-input-custom" 
                           value="{{ old('due_date') }}" 
                           id="due_date">
                </div>
                <div class="form-group-custom">
                    <label class="form-label-custom">
                        <i class="fas fa-clock"></i>
                        Jam Deadline
                    </label>
                    <input type="time" name="due_time" class="form-input-custom" 
                           value="{{ old('due_time') }}" 
                           id="due_time">
                    <small class="form-hint">Opsional. Jika tidak diisi, deadline akan diatur ke akhir hari (23:59)</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Simpan Tugas
                </button>
                <a href="{{ route('tasks.index') }}" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/create-task.js') }}"></script>
@endsection