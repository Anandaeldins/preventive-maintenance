@extends('layouts.bar')

@section('title', 'Dashboard Pusat')

@push('style')
    <style>
        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', sans-serif;
        }

        .page-title {
            font-weight: 700;
            margin-bottom: 20px;
        }

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

        .stat-card {
            padding: 24px;
            text-align: center;
            background: #fff;
            transition: 0.3s;
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
        }

        .table td {
            padding: 16px;
            border: none;
        }

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

        <h4 class="page-title">Dashboard Pusat</h4>

        {{-- ================= STAT ================= --}}
        <div class="row g-4 mb-4">

            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="stat-icon"><i class="bi bi-clipboard-data"></i></div>
                    <div class="stat-number">{{ $totalReports }}</div>
                    <div class="stat-label">Total Laporan</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                    <div class="stat-number">{{ $pendingRO }}</div>
                    <div class="stat-label">Pending RO</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="stat-icon"><i class="bi bi-building"></i></div>
                    <div class="stat-number">{{ $pendingPusat }}</div>
                    <div class="stat-label">Pending Pusat</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                    <div class="stat-number">{{ $approved }}</div>
                    <div class="stat-label">Approved</div>
                </div>
            </div>

        </div>

        {{-- ================= CHART ================= --}}
        <div class="card mb-4">
            <div class="card-header">
                Distribusi Laporan per Regional
            </div>
            <div class="card-body">
                <div style="height:300px;">
                    <canvas id="regionalChart"></canvas>
                </div>
            </div>
        </div>

        {{-- ================= TABLE ================= --}}
        <div class="card">
            <div class="card-header">
                Laporan Terbaru
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Regional</th>
                                <th>Segment</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($latestReports as $r)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $r->creator->regional->nama_regional ?? '-' }}</td>
                                    <td>{{ $r->segment_inspeksi }}</td>
                                    <td>{{ \Carbon\Carbon::parse($r->tanggal_inspeksi)->format('d M Y') }}</td>

                                    <td>
                                        @if ($r->status_workflow == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($r->status_workflow == 'pending_ro')
                                            <span class="badge bg-info">Pending RO</span>
                                        @elseif($r->status_workflow == 'pending_pusat')
                                            <span class="badge bg-primary">Pending Pusat</span>
                                        @else
                                            <span class="badge bg-secondary">Draft</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Tidak ada data</td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- ================= CHART SCRIPT ================= --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const ctx = document.getElementById('regionalChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($regionalLabels),
                datasets: [{
                    data: @json($regionalCounts),
                    backgroundColor: 'rgba(59,130,246,0.85)',
                    borderRadius: 12
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
@endsection
