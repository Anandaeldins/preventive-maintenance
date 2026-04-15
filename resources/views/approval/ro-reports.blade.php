@extends('layouts.bar')
@push('style')
    <style>
        .fmea-wrapper {
            padding-top: 20px;
        }

        .fmea-card {
            background: var(--card);
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 20px 50px rgba(37, 99, 235, 0.15);
        }

        .fmea-header h4 {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .fmea-header p {
            color: var(--muted);
            font-size: 14px;
        }

        /* TABLE */
        .table-modern table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-modern th {
            text-align: left;
            font-size: 13px;
            color: var(--muted);
            padding-bottom: 10px;
        }

        .table-modern td {
            padding: 14px 0;
            border-top: 1px solid #e2e8f0;
        }

        /* ROW */
        .row-hover {
            transition: 0.2s;
        }

        .row-hover:hover {
            background: rgba(37, 99, 235, 0.05);
        }

        /* BADGE */
        .badge-modern {
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-modern.warning {
            background: #fef9c3;
            color: #ca8a04;
        }

        .badge-modern.primary {
            background: #dbeafe;
            color: #2563eb;
        }

        .badge-modern.success {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge-modern.danger {
            background: #fee2e2;
            color: #dc2626;
        }

        /* BUTTON */
        .btn-outline-modern {
            border: 1px solid #2563eb;
            color: #2563eb;
            border-radius: 8px;
        }

        .btn-outline-modern:hover {
            background: #2563eb;
            color: white;
        }

        /* ALERT */
        .alert-modern {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alert-modern.success {
            background: #dcfce7;
            color: #16a34a;
        }

        .alert-modern.error {
            background: #fee2e2;
            color: #dc2626;
        }
    </style>
@endpush
@section('content')
    <div class="fmea-wrapper">

        <div class="fmea-card">

            <div class="fmea-header">
                <h4>Approval Laporan Inspeksi (Kepala RO)</h4>
                <p>Daftar laporan yang menunggu persetujuan</p>
            </div>

            {{-- ALERT --}}
            @if (session('success'))
                <div class="alert-modern success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert-modern error">{{ session('error') }}</div>
            @endif

            <div class="table-modern">
                <table>
                    <thead>
                        <tr>
                            <th>Segment</th>
                            <th>Tanggal</th>
                            <th>Teknisi</th>
                            <th>Jalur</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($reports as $report)
                            <tr class="row-hover">

                                <td>{{ $report->segment_inspeksi }}</td>

                                <td>
                                    {{ \Carbon\Carbon::parse($report->tanggal_inspeksi)->format('d F Y') }}
                                </td>

                                <td>{{ $report->nama_pelaksana }}</td>

                                <td>{{ ucfirst(str_replace('_', ' ', $report->jalur_fo)) }}</td>

                                <td>
                                    @if ($report->status_workflow == 'pending_ro')
                                        <span class="badge-modern warning">Pending RO</span>
                                    @elseif ($report->status_workflow == 'pending_pusat')
                                        <span class="badge-modern primary">Pending Pusat</span>
                                    @elseif ($report->status_workflow == 'approved')
                                        <span class="badge-modern success">Approved</span>
                                    @elseif ($report->status_workflow == 'rejected')
                                        <span class="badge-modern danger">Rejected</span>
                                    @endif
                                </td>

                                <td>
                                    <button class="btn btn-sm btn-outline-modern view-report" data-id="{{ $report->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Tidak ada laporan menunggu approval
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    </div>
    <div class="modal fade" id="reportModal" tabindex="-1">

        <div class="modal-dialog modal-xl">

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Detail Laporan Inspeksi</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="reportContent">

                    Loading...

                </div>

            </div>
        </div>
    </div>
    </div>


    @push('scripts')
        <script>
            $(document).on('click', '.view-report', function() {

                let id = $(this).data('id');

                let modal = new bootstrap.Modal(document.getElementById('reportModal'));
                modal.show();

                $('#reportContent').html('Loading...');

                $.ajax({
                    url: '/report/modal/' + id,
                    type: 'GET',
                    success: function(data) {
                        $('#reportContent').html(data);
                    },
                    error: function() {
                        $('#reportContent').html('<div class="text-danger">Gagal memuat laporan</div>');
                    }
                });

            });
        </script>
    @endpush
@endsection
