@extends('layouts.bar')

@section('title', 'Fitur Admin')

@push('style')
    <style>
        body {
            background: linear-gradient(135deg, #6aa5ff, #3b82f6);
        }

        /* ================= PAGE BACKGROUND ================= */
        .page-bg {
            background: linear-gradient(180deg, #ffffff82 80%, #ffffff82 0%);
            border-radius: 18px;
            min-height: 50vh;
            padding: 28px 44px;
            transition: 0.4s ease;
        }

        body.dark .page-bg {
            background: linear-gradient(180deg, #0f172a 0%, #020617 100%);
        }

        /* ================= FEATURES GRID ================= */
        .features {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }

        /* ================= CARD ================= */
        .card {
            background: #ffffff;
            border-radius: 18px;
            padding: 30px 32px;
            height: 240px;
            /* ⬅️ INI KUNCINYA */
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.12);
            border: 1px solid #e6efff;
            transition: 0.3s ease;

            display: flex;
            /* ⬅️ flex */
            flex-direction: column;
        }


        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 35px rgba(59, 130, 246, 0.22);
        }

        /* ================= ICON ================= */
        .card .icon {
            width: 50px;
            height: 56px;
            border-radius: 10px;
            background: linear-gradient(135deg, #6aa5ff, #3b82f6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 22px;
            margin-bottom: 14px;
        }

        .card h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #0f172a;
        }

        .card p {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
        }

        /* ================= LINK WRAPPER ================= */
        .card-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .card-link .card {
            cursor: pointer;
        }

        .card-link:hover .card {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 22px 40px rgba(59, 130, 246, 0.28);
        }

        /* ================= DARK MODE (FIXED – SAME FEEL) ================= */
        body.dark {
            background: linear-gradient(135deg, #0b2751, #0c254d);
        }

        body.dark .card {
            background: #020617;
            /* tetap gelap */
            border: 3px solid #647ba2;
            /* tipis & rapi */
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.15);
            /* SAMA feel sebelumnya */
        }

        body.dark .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 35px rgba(57, 117, 247, 0.589);
        }

        body.dark .card h3 {
            color: #e5e7eb;
        }

        body.dark .card p {
            color: #94a3b8;
        }

        body.dark .card .icon {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }


        /* ================= RESPONSIVE ================= */
        @media (max-width: 1024px) {
            .features {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .features {
                grid-template-columns: 1fr;
            }

            .page-bg {
                padding: 32px 20px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="page-bg">

        <div class="features">

            <!-- Penjadwalan -->
            @auth
                @if (auth()->user()->role == 'teknisi')
                    <a href="{{ route('pm-schedules.index') }}" class="card-link" onclick="event.stopPropagation();">
                        <div class="card">
                            <div class="icon"><i class="bi bi-calendar-event"></i></div>
                            <h3>Penjadwalan PM</h3>
                            <p>Atur jadwal pekerjaan dan aktivitas operasional secara terstruktur agar tidak terlewat.</p>
                        </div>
                    </a>
                @endif
            @endauth

            <!-- Task Maintenance -->
            @auth
                @if (auth()->user()->role == 'teknisi')
                    <a href="{{ route('tasks.index') }}" class="card-link" onclick="event.stopPropagation();">
                        <div class="card">
                            <div class="icon"><i class="bi bi-tools"></i></div>
                            <h3>Task Maintenance</h3>
                            <p>Kelola kegiatan preventive dan corrective maintenance dengan pencatatan yang rapi.</p>
                        </div>
                    </a>
                @endif
            @endauth

            <!-- Approval -->
            @auth
                @if (auth()->user()->role == 'kepala_ro')
                    <a href="{{ route('approval.pending.schedules') }}" class="card-link">
                        <div class="card">
                            <div class="icon"><i class="bi bi-people"></i></div>
                            <h3>Pending Schedules</h3>
                            <p>Atur pending jadwal</p>
                        </div>
                    </a>
                @endif
            @endauth
            <!-- Approval PM -->
            @auth
                @if (auth()->user()->role == 'kepala_ro')
                    <a href="{{ route('approval.ro.reports') }}" class="card-link">
                        <div class="card">
                            <div class="icon"><i class="bi bi-people"></i></div>
                            <h3>Pending PM</h3>
                            <p>Atur pending PM</p>
                        </div>
                    </a>
                @endif
            @endauth
            <!-- Manajemen User -->
            @auth
                @if (in_array(auth()->user()->role, ['admin']))
                    <a href="/admin/users" class="card-link">
                        <div class="card">
                            <div class="icon"><i class="bi bi-people"></i></div>
                            <h3>Manajemen User</h3>
                            <p>Atur akun, peran, dan hak akses pengguna sesuai kebutuhan sistem.</p>
                        </div>
                    </a>
                @endif
            @endauth

            <!-- Manajemen User -->
            @auth
                @if (auth()->user()->role == 'kepala_ro')
                    <a href="/fmeaoutput" class="card-link">
                        <div class="card">
                            <div class="icon"><i class="bi bi-people"></i></div>
                            <h3>Hasil FMEA</h3>
                            <p>melihat hasil fmea persegment</p>
                        </div>
                    </a>
                @endif
            @endauth


            <!-- Approval Pusat PM -->
            @auth
                @if (auth()->user()->role == 'pusat')
                    <a href="{{ route('approval.pusat.reports') }}" class="card-link">
                        <div class="card">
                            <div class="icon"><i class="bi bi-people"></i></div>
                            <h3>Pending PM</h3>
                            <p>Atur pending PM</p>
                        </div>
                    </a>
                @endif
            @endauth

            <!-- Laporan -->
            @auth
                @if (in_array(auth()->user()->role, ['teknisi', 'kepala_ro']))
                    <a href="{{ route('maintenance.info') }}" class="card-link">
                        <div class="card">
                            <div class="icon"><i class="bi bi-bar-chart"></i></div>
                            <h3>Laporan PM</h3>
                            <p>Lihat rekap data, status pekerjaan, dan histori aktivitas.</p>
                        </div>
                    </a>
                @endif
            @endauth

            <!-- Segments -->
            @auth
                @if (auth()->user()->role == 'teknisi')
                    <a href="/segments" class="card-link">
                        <div class="card">
                            <div class="icon"><i class="bi bi-diagram-3"></i></div>
                            <h3>Segments</h3>
                            <p>Kelola segment untuk pembagian area atau kategori dalam sistem preventive maintenance.</p>
                        </div>
                    </a>
                @endif
            @endauth



        </div>
    @endsection
