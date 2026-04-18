@extends('layouts.bar')

@section('title', 'Dashboard Teknisi')

@push('style')
    <style>
        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', sans-serif;
        }

        /* ================= HEADER ================= */
        .page-title {
            font-weight: 700;
            margin-bottom: 20px;
        }

        /* ================= CARD GLOBAL ================= */
        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.12);
            border: 1px solid #e6efff;
        }

        .card-header {
            background: linear-gradient(135deg, #4A90E2, #2563eb);
            color: white;
            padding: 16px 20px;
            font-weight: 600;
            border-radius: 18px 18px 0 0;
        }

        .card-body {
            padding: 20px;
        }

        /* ================= STAT CARD ================= */
        .stat-card {
            padding: 24px;
            text-align: center;
            transition: 0.3s;
            background: #fff;
        }

        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 35px rgba(59, 130, 246, 0.2);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6aa5ff, #3b82f6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 20px;
        }

        .stat-number {
            font-size: 28px;
            font-weight: bold;
        }

        .stat-label {
            color: #64748b;
            font-size: 14px;
        }

        /* ================= TABLE ================= */
        .table {
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .table thead th {
            font-size: 12px;
            text-transform: uppercase;
            color: #64748b;
            border: none;
        }

        .table tbody tr {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
            transition: 0.2s;
        }

        .table tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08);
        }

        .table td {
            padding: 16px;
            border: none;
        }

        /* ================= BADGE ================= */
        .badge {
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
        }

        .badge.bg-success {
            background: #dcfce7 !important;
            color: #16a34a !important;
        }

        .badge.bg-info {
            background: #e0f2fe !important;
            color: #0284c7 !important;
        }

        .badge.bg-primary {
            background: #e0e7ff !important;
            color: #4f46e5 !important;
        }

        .badge.bg-secondary {
            background: #f1f5f9 !important;
            color: #475569 !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4">


        <h4 class="page-title">Dashboard Teknisi</h4>

        {{-- ================= STAT ================= --}}
        <div class="row g-4 mb-4">

            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="stat-icon"><i class="bi bi-clipboard-data"></i></div>
                    <div class="stat-number">{{ $totalTask }}</div>
                    <div class="stat-label">Total Laporan</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                    <div class="stat-number">{{ $pendingTask }}</div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                    <div class="stat-number">{{ $completedTask }}</div>
                    <div class="stat-label">Selesai</div>
                </div>
            </div>

        </div>
        <div class="card mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <h6 class="text-muted mb-1">Jadwal Terdekat</h6>

                    @if ($nextSchedule)
                        <h5 class="mb-0 fw-bold">
                            {{ $nextSchedule->nama_segment }}
                        </h5>

                        <small class="text-muted">
                            {{ \Carbon\Carbon::parse($nextSchedule->planned_date)->format('d M Y') }}
                            • {{ \Carbon\Carbon::parse($nextSchedule->planned_date)->diffForHumans() }}
                        </small>
                    @else
                        <h6 class="text-muted">Tidak ada jadwal</h6>
                    @endif
                </div>

                <div>
                    <i class="bi bi-calendar-check fs-1 text-primary"></i>
                </div>

            </div>
        </div>
        {{-- ================= CHART ================= --}}
        <div class="card mb-4">
            <div class="card-header">
                Distribusi Jadwal PM (Approved)
            </div>
            <div class="card-body">
                <canvas id="scheduleChart"></canvas>
            </div>
        </div>

        {{-- ================= TABLE ================= --}}
        <div class="card">
            <div class="card-header">
                Laporan PM Terbaru
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Segment</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($tasks as $task)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $task->segment_inspeksi }}</td>
                                    <td>{{ \Carbon\Carbon::parse($task->tanggal_inspeksi)->format('d M Y') }}</td>
                                    <td>
                                        @php
                                            $status = $task->status_workflow;
                                        @endphp

                                        @if ($status == 'approved')
                                            <span class="badge bg-success">Selesai</span>
                                        @elseif($status == 'pending_ro')
                                            <span class="badge bg-info">Pending RO</span>
                                        @elseif($status == 'pending_pusat')
                                            <span class="badge bg-primary">Pending Pusat</span>
                                        @else
                                            <span class="badge bg-secondary">Draft</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Tidak ada data</td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
        ```

    </div>

    {{-- ================= CHART SCRIPT ================= --}}

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const ctx = document.getElementById('scheduleChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($labels),
                datasets: [{
                    label: 'Jumlah Jadwal',
                    data: @json($values),

                    backgroundColor: [
                        'rgba(59,130,246,0.85)', // blue
                        'rgba(16,185,129,0.85)', // emerald
                        'rgba(245,158,11,0.85)', // amber
                        'rgba(239,68,68,0.85)', // red
                        'rgba(139,92,246,0.85)', // purple
                        'rgba(148,163,184,0.85)' // gray
                    ],

                    hoverBackgroundColor: [
                        'rgba(59,130,246,1)',
                        'rgba(16,185,129,1)',
                        'rgba(245,158,11,1)',
                        'rgba(239,68,68,1)',
                        'rgba(139,92,246,1)',
                        'rgba(148,163,184,1)'
                    ],

                    borderRadius: 12,
                    borderSkipped: false,
                    maxBarThickness: 40
                }]
            },

            options: {
                responsive: true,

                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                },

                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#fff',
                        bodyColor: '#cbd5f5',
                        padding: 10,
                        cornerRadius: 8
                    }
                },

                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b'
                        }
                    }
                }
            }
        });
    </script>
@endsection
