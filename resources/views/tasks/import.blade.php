@extends('layouts.app')

@section('content')
<div class="task-list-container">
    <div class="task-list-wrapper">
        {{-- Header Section --}}
        <div class="page-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-file-import"></i>
                </div>
                <div>
                    <h2 class="page-title">Import Tugas</h2>
                    <p class="page-subtitle">Import tugas dari file CSV, Excel, atau Google Sheets</p>
                </div>
            </div>
            <div class="header-buttons">
                <a href="{{ route('tasks.index') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        {{-- Alert Section --}}
        @if(session('success'))
            <div class="alert-custom alert-success-custom">
                <div class="alert-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="alert-content">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert-custom alert-danger-custom">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="alert-content">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        {{-- Import Options --}}
        <div class="import-container">
            <div class="import-options">
                {{-- Option 1: File Upload --}}
                <div class="import-option-card">
                    <div class="option-icon">
                        <i class="fas fa-file-upload"></i>
                    </div>
                    <div class="option-content">
                        <h3>Import dari File</h3>
                        <p>Upload file CSV, Excel, atau TXT</p>
                        
                        <form action="{{ route('tasks.import') }}" method="POST" enctype="multipart/form-data" class="import-form">
                            @csrf
                            <div class="form-group">
                                <label for="import_file">Pilih File</label>
                                <input type="file" name="import_file" id="import_file" accept=".csv,.xlsx,.xls,.txt" required class="file-input">
                                <small class="form-text">Format: CSV, Excel (XLSX), atau TXT</small>
                            </div>
                            <button type="submit" class="btn-import">
                                <i class="fas fa-upload"></i> Upload & Import
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Option 2: URL Import --}}
                <div class="import-option-card">
                    <div class="option-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    <div class="option-content">
                        <h3>Import dari URL</h3>
                        <p>Import dari Google Sheets atau URL file</p>
                        
                        <form action="{{ route('tasks.import') }}" method="POST" class="import-form">
                            @csrf
                            <div class="form-group">
                                <label for="import_url">URL File</label>
                                <input type="url" name="import_url" id="import_url" 
                                       placeholder="https://docs.google.com/spreadsheets/..." 
                                       class="url-input">
                                <small class="form-text">Support: Google Sheets (export as CSV/TSV), atau direct file URL</small>
                            </div>
                            <button type="submit" class="btn-import">
                                <i class="fas fa-download"></i> Import dari URL
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="guide-section">
    <h4>Struktur Kolom:</h4>
    <div class="table-responsive">
        <table class="format-table">
            <thead>
                <tr>
                    <th>JudulTugas*</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th>Deadline</th>
                    <th>WaktuDeadline</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Meeting client</td>
                    <td>Presentasi progress proyek</td>
                    <td>Pending</td>
                    <td>15/01/2024</td>
                    <td>14:30</td>
                </tr>
                <tr>
                    <td>Belanja bulanan</td>
                    <td>Beli bahan makanan</td>
                    <td>Selesai</td>
                    <td>10/01/2024</td>
                    <td>09:00</td>
                </tr>
            </tbody>
        </table>
    </div>
    <small>*Kolom wajib: JudulTugas. Kolom lain optional.</small>
    <small>Format tanggal: DD/MM/YYYY (contoh: 15/01/2024)</small>
    <small>Format waktu: HH:MM (contoh: 14:30)</small>
    <small>Format file: CSV dengan separator titik koma (;)</small>
</div>

                    <div class="guide-actions">
                        <a href="{{ route('tasks.import.template') }}" class="btn-template">
                            <i class="fas fa-download"></i> Download Template
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.import-container {
    max-width: 1000px;
    margin: 0 auto;
}

.import-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 3rem;
}

.import-option-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: 1px solid #e1e5e9;
}

.option-icon {
    font-size: 3rem;
    color: #3b82f6;
    margin-bottom: 1rem;
    text-align: center;
}

.option-content h3 {
    margin-bottom: 0.5rem;
    color: #1f2937;
}

.option-content p {
    color: #6b7280;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}

.file-input, .url-input {
    width: 100%;
    padding: 0.75rem;
    border: 2px dashed #d1d5db;
    border-radius: 8px;
    background: #f9fafb;
}

.file-input:focus, .url-input:focus {
    outline: none;
    border-color: #3b82f6;
    background: white;
}

.form-text {
    display: block;
    margin-top: 0.5rem;
    color: #6b7280;
    font-size: 0.875rem;
}

.btn-import {
    background: #3b82f6;
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: background 0.2s;
}

.btn-import:hover {
    background: #2563eb;
}

.import-guide {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: 1px solid #e1e5e9;
}

.guide-header {
    border-bottom: 2px solid #f3f4f6;
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
}

.guide-header h3 {
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.guide-section {
    margin-bottom: 2rem;
}

.guide-section h4 {
    color: #374151;
    margin-bottom: 0.75rem;
}

.guide-section ul, .guide-section ol {
    padding-left: 1.5rem;
    color: #6b7280;
}

.guide-section li {
    margin-bottom: 0.5rem;
}

.format-table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}

.format-table th,
.format-table td {
    border: 1px solid #e5e7eb;
    padding: 0.75rem;
    text-align: left;
}

.format-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
}

.format-table td {
    color: #6b7280;
}

.btn-template {
    background: #10b981;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    transition: background 0.2s;
}

.btn-template:hover {
    background: #059669;
    color: white;
}

.btn-back {
    background: #6b7280;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    transition: background 0.2s;
}

.btn-back:hover {
    background: #4b5563;
    color: white;
}

.alert-custom {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.alert-success-custom {
    background: #d1fae5;
    border: 1px solid #a7f3d0;
    color: #065f46;
}

.alert-danger-custom {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
}

@media (max-width: 768px) {
    .import-options {
        grid-template-columns: 1fr;
    }
    
    .import-option-card {
        padding: 1.5rem;
    }
}
</style>
@endsection