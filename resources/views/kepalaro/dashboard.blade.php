@extends('layouts.bar')

@section('title', 'Dashboard Kepala RO')

@push('style')
    <style>
        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', sans-serif;
        }

        .page-title {
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center;
        }

        /* ===== CARD GLOBAL ===== */
        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.12);
            border: 1px solid #e6efff;
        }

        .card-body {
            padding: 20px;
        }

        /* ===== STAT CARD ===== */
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
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 20px;
        }

        .icon-blue {
            background: linear-gradient(135deg, #6aa5ff, #3b82f6);
        }

        .icon-yellow {
            background: linear-gradient(135deg, #facc15, #eab308);
        }

        .icon-purple {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        .icon-green {
            background: linear-gradient(135deg, #22c55e, #16a34a);
        }

        .stat-number {
            font-size: 28px;
            font-weight: bold;
        }

        .stat-label {
            color: #64748b;
            font-size: 14px;
        }

        /* ===== TABLE ===== */
        .table-modern {
            border-radius: 18px;
            overflow: hidden;
            background: white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .table-modern thead {
            background: #f8fafc;
        }

        .table-modern th {
            padding: 16px;
            font-weight: 600;
            color: #64748b;
        }

        .table-modern td {
            padding: 16px;
        }

        .table-modern tbody tr:hover {
            background: rgba(59, 130, 246, 0.05);
        }

        /* ===== BADGE ===== */
        .badge-modern {
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
        }

        .badge-info {
            background: #e0f2fe;
            color: #0284c7;
        }

        .badge-success {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge-primary {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .badge-secondary {
            background: #f1f5f9;
            color: #475569;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4">

        {{-- HEADER --}}
        <div class="page-title">
            <h4 class="fw-bold">Dashboard Kepala RO</h4>
            <small class="text-muted">
                {{ auth()->user()->username }} • {{ now()->format('d M Y') }}
            </small>
        </div>

        {{-- STAT --}}
        <div class="row g-4 mb-5">

            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="stat-icon icon-blue"><i class="bi bi-clipboard-data"></i></div>
                    <div class="stat-number text-primary">{{ $totalReports }}</div>
                    <div class="stat-label">Total Laporan</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="stat-icon icon-blue"><i class="bi bi-hourglass-split"></i></div>
                    <div class="stat-number text-primary">{{ $pendingRO }}</div>
                    <div class="stat-label">Pending RO</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="stat-icon icon-blue"><i class="bi bi-building"></i></div>
                    <div class="stat-number text-primary">{{ $pendingPusat }}</div>
                    <div class="stat-label">Pending Pusat</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="stat-icon icon-blue"><i class="bi bi-check-circle"></i></div>
                    <div class="stat-number text-primary">{{ $approvedReports }}</div>
                    <div class="stat-label">Disetujui</div>
                </div>
            </div>

        </div>

        {{-- TABLE --}}
        <h5 class="fw-bold mb-3">Laporan Menunggu Approval</h5>

        <div class="table-modern">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Segment</th>
                        <th>Tanggal</th>
                        <th>Teknisi</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $report->segment_inspeksi }}</td>
                            <td>{{ optional($report->tanggal_inspeksi)->format('d M Y') }}</td>
                            <td>{{ optional($report->preparer)->username }}</td>

                            <td>
                                @php $status = $report->status_workflow; @endphp

                                @if ($status == 'pending_ro')
                                    <span class="badge-modern badge-info">Pending RO</span>
                                @elseif($status == 'approved')
                                    <span class="badge-modern badge-success">Approved</span>
                                @elseif($status == 'pending_pusat')
                                    <span class="badge-modern badge-primary">Pending Pusat</span>
                                @else
                                    <span class="badge-modern badge-secondary">Draft</span>
                                @endif
                            </td>

                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Tidak ada data
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection
