@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Tambah Tugas</h2>

    {{-- Pesan error validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan!</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>- {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Tambah Tugas --}}
    <form action="{{ route('tasks.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Judul Tugas</label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Deadline</label>
                <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}" id="due_date">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Jam Deadline</label>
                <input type="time" name="due_time" class="form-control" value="{{ old('due_time') }}" id="due_time">
                <small class="form-text text-muted">Opsional. Jika tidak diisi, deadline akan diatur ke akhir hari (23:59)</small>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>

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