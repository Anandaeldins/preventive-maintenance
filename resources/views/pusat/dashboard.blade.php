@extends('layouts.bar')

@section('title', 'Dashboard Pusat')

@section('content')
    <div class="page-bg">
        <h2>Dashboard Pusat</h2>

        <div class="features">

            <a href="{{ route('approval.pusat.reports') }}" class="card-link">
                <div class="card">
                    <div class="icon"><i class="bi bi-building"></i></div>
                    <h3>Approval Pusat</h3>
                    <p>Review laporan dari seluruh cabang.</p>
                </div>
            </a>

            <a href="{{ route('settings.index') }}" class="card-link">
                <div class="card">
                    <div class="icon"><i class="bi bi-gear"></i></div>
                    <h3>Pengaturan</h3>
                    <p>Kelola konfigurasi sistem.</p>
                </div>
            </a>

        </div>
    </div>
@endsection
