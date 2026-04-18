@extends('layouts.bar')

@section('title', 'Dashboard Admin')

@push('style')
    <style>
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
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4">

        <h4 class="page-title">Dashboard Admin</h4>

        <div class="row g-4">

            {{-- TOTAL USER --}}
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-number">{{ $totalUser }}</div>
                    <div class="stat-label">Total User</div>
                </div>
            </div>

            {{-- TOTAL LAPORAN --}}
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-clipboard-data"></i>
                    </div>
                    <div class="stat-number">{{ $totalLaporan }}</div>
                    <div class="stat-label">Total Laporan</div>
                </div>
            </div>

            {{-- PENDING --}}
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="stat-number">{{ $pending }}</div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>

            {{-- APPROVED --}}
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-number">{{ $approved }}</div>
                    <div class="stat-label">Approved</div>
                </div>
            </div>

        </div>
        {{-- ================= TABLE LAPORAN TERBARU ================= --}}
        <div class="card mt-5">
            <div class="card-header">
                Laporan Terbaru
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Segment</th>
                                <th>Teknisi</th>
                                <th>Regional</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($latestReports as $report)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>{{ $report->segment_inspeksi }}</td>

                                    <td>{{ optional($report->preparer)->username }}</td>

                                    <td>
                                        {{ optional(optional($report->preparer)->regional)->nama_regional ?? '-' }}
                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($report->tanggal_inspeksi)->format('d M Y') }}
                                    </td>

                                    <td>
                                        @php $status = $report->status_workflow; @endphp

                                        @if ($status == 'approved')
                                            <span class="badge bg-success">Approved</span>
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
                                    <td colspan="6" class="text-center text-muted">
                                        Tidak ada data
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection
