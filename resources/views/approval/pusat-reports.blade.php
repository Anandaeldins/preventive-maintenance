@extends('layouts.bar')

@push('style')
    <style>
        /* WRAPPER */
        .fmea-wrapper {
            padding-top: 20px;
        }

        .fmea-card {
            background: var(--card);
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 20px 50px rgba(37, 99, 235, 0.15);
        }

        /* HEADER */
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

        .table-modern tr:hover {
            background: rgba(37, 99, 235, 0.05);
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
    </style>
@endpush

@section('content')
    <div class="fmea-wrapper">
        <div class="fmea-card">

            <div class="fmea-header">
                <h4>Laporan Menunggu Approval Pusat</h4>
                <p>Daftar laporan yang menunggu persetujuan pusat</p>
            </div>

            <div class="table-modern">
                <table>
                    <thead>
                        <tr>
                            <th>Regional</th>
                            <th>Segment</th>
                            <th>Tanggal</th>
                            <th>Pelaksana</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($reports as $r)
                            <tr>
                                <td>{{ $r->creator->regional->nama_regional ?? '-' }}</td>
                                <td>{{ $r->segment_inspeksi }}</td>

                                <td>{{ \Carbon\Carbon::parse($r->tanggal_inspeksi)->format('d F Y') }}</td>

                                <td>{{ $r->nama_pelaksana }}</td>

                                <td>
                                    <button class="btn btn-sm btn-outline-modern view-report" data-id="{{ $r->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- MODAL --}}
    <div class="modal fade" id="reportModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Detail Laporan</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="reportContent">
                    Loading...
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).on('click', '.view-report', function() {

            let id = $(this).data('id');

            let modal = new bootstrap.Modal(document.getElementById('reportModal'));
            modal.show();

            $('#reportContent').html('Loading...');

            let url = "{{ route('report.modal', ['id' => 'ID']) }}".replace('ID', id);

            $.get(url, function(data) {
                $('#reportContent').html(data);
            });

        });
    </script>
@endpush
