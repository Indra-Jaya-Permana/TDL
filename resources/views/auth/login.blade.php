@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">

<div class="login-container">
    <div class="login-card">
        <div class="decorative-circle circle-1"></div>
        <div class="decorative-circle circle-2"></div>
        
        <h3 class="login-title">Selamat Datang</h3>
        <p class="login-subtitle">Silakan masuk untuk melanjutkan</p>

        {{-- Pesan Error --}}
        @if(session('error'))
            <div class="alert-custom">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Login dengan Google --}}
        <a href="{{ route('google.login') }}" class="btn-google">
            <i class="fab fa-google"></i>
            <span>Masuk dengan Google</span>
        </a>

        <div class="footer-text">
            Dengan masuk, Anda menyetujui syarat dan ketentuan kami
        </div>
    </div>
</div>
@endsection