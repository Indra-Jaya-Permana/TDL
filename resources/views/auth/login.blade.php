@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-lg p-4" style="width: 400px; border-radius: 12px;">
        <h3 class="text-center mb-4">Login</h3>

        {{-- Pesan Error --}}
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Login dengan Google --}}
        <a href="{{ route('google.login') }}" class="btn btn-danger w-100">
            <i class="fab fa-google"></i> Login dengan Google
        </a>
    </div>
</div>
@endsection
