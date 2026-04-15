@extends('layouts.bar')

@section('title', 'Dashboard Kepala RO')

@section('content')
    <div class="page-bg">
        <h2>Dashboard Kepala RO</h2>

        <div class="features">

            <a href="{{ route('approval.pending.schedules') }}" class="card-link">
                <div class="card">
                    <div class="icon"><i class="bi bi-calendar-check"></i></div>
                    <h3>Approval Jadwal</h3>
                    <p>Lihat dan approve jadwal PM dari teknisi.</p>
                </div>
            </a>

            <a href="{{ route('approval.ro.reports') }}" class="card-link">
                <div class="card">
                    <div class="icon"><i class="bi bi-clipboard-check"></i></div>
                    <h3>Approval Laporan</h3>
                    <p>Review laporan maintenance yang diajukan.</p>
                </div>
            </a>

            <a href="{{ route('fmea.page') }}" class="card-link">
                <div class="card">
                    <div class="icon"><i class="bi bi-graph-up"></i></div>
                    <h3>FMEA Output</h3>
                    <p>Lihat hasil analisis risiko (FMEA).</p>
                </div>
            </a>

            <a href="{{ route('maintenance.info') }}" class="card-link">
                <div class="card">
                    <div class="icon"><i class="bi bi-bar-chart"></i></div>
                    <h3>Laporan PM</h3>
                    <p>Monitoring aktivitas maintenance.</p>
                </div>
            </a>

        </div>
    </div>
@endsection
