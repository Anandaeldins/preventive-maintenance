@extends('layouts.bar')
<style>
    /* GLOBAL */
    body {
        background: #f1f5f9;
        font-family: 'Segoe UI', sans-serif;
    }

    /* CARD */
    .card {
        border: none;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .card-header {
        background: linear-gradient(135deg, #4A90E2, #2563eb);
        color: white;
        padding: 18px 24px;
        font-size: 18px;
        font-weight: 600;
    }

    .card-body {
        padding: 24px;
    }

    /* FILTER */
    .pm-filter-bar {
        background: #ffffff;
        padding: 18px;
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }

    .pm-filter-select {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 14px;
        transition: 0.2s;
        background: #f9fafb;
    }

    .pm-filter-select:focus {
        border-color: #3b82f6;
        background: #fff;
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    .pm-filter-apply {
        background: linear-gradient(135deg, #4A90E2, #2563eb);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 10px 18px;
        font-weight: 500;
        transition: 0.2s;
    }

    .pm-filter-apply:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.3);
    }

    /* TABLE */
    .table {
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    .table thead th {
        font-size: 13px;
        text-transform: uppercase;
        color: #64748b;
        border: none;
    }

    .table tbody tr {
        background: #ffffff;
        transition: 0.2s;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
    }

    .table tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08);
    }

    .table td {
        padding: 16px;
        border: none;
    }

    /* BADGE STATUS */
    .badge {
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 500;
    }

    /* STATUS CUSTOM */
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

    .badge.bg-danger {
        background: #fee2e2 !important;
        color: #dc2626 !important;
    }

    /* BUTTON AKSI */
    .action-btn {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        border: none;
        transition: 0.2s;
    }

    .btn-view {
        background: linear-gradient(135deg, #06b6d4, #0891b2);
    }

    .action-group {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: nowrap;
        gap: 8px;
    }

    .action-group form {
        margin: 0;
        display: inline-flex;
    }

    .btn-send {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
    }

    .btn-edit {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .btn-delete {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .aksi-cell {
        min-width: 190px;
        text-align: center;
        vertical-align: middle;
    }

    .edit-iframe {
        width: 100%;
        height: 82vh;
        border: 0;
    }

    .action-btn:hover {
        transform: scale(1.08);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
    }

    /* TEXT MUTED */
    .text-muted {
        font-size: 13px;
    }

    /* MODAL */
    .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        border-bottom: none;
        font-weight: 600;
    }

    .modal-footer {
        border-top: none;
    }

    /* BUTTON MODAL */
    #btnKirimRO {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        border: none;
        border-radius: 10px;
        padding: 10px 18px;
        font-weight: 500;
    }

    #btnKirimRO:hover {
        box-shadow: 0 6px 14px rgba(34, 197, 94, 0.3);
    }

    /* SCROLL TABLE */
    .table-responsive {
        margin-top: 10px;
    }

    /* NAVBAR (optional kalau mau) */
    .navbar {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.8) !important;
    }

    .btn-back {
        background: rgba(255, 255, 255, 0.15);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 500;
        backdrop-filter: blur(6px);
        transition: 0.2s;
        text-decoration: none;
    }

    .btn-back:hover {
        background: rgba(255, 255, 255, 0.25);
        color: #fff;
        transform: translateY(-1px);
    }
</style>


@section('content')
    <div class="card">

        <div class="card-header text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Informasi Maintenance</h5>

            <a href="{{ url('/tasks') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin:0; padding-left:18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="pm-filter-bar">
                <form method="GET" class="d-flex gap-2 flex-wrap">



                    <select name="sort" class="pm-filter-select">
                        <option value="">Urutkan</option>
                        <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Tanggal Terdekat</option>
                        <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Tanggal Terjauh</option>
                    </select>

                    <select name="segment" class="pm-filter-select">
                        <option value="">Semua Segment</option>
                        @foreach ($allSegments as $seg)
                            <option value="{{ $seg->id }}" {{ request('segment') == $seg->id ? 'selected' : '' }}>
                                {{ $seg->nama_segment }}
                            </option>
                        @endforeach
                    </select>
                    <select name="status" class="pm-filter-select">
                        <option value="">Semua Status</option>
                        <option value="belum" {{ request('status') == 'belum' ? 'selected' : '' }}>Belum Dikerjakan
                        </option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending_ro" {{ request('status') == 'pending_ro' ? 'selected' : '' }}>Pending RO
                        </option>
                        <option value="pending_pusat" {{ request('status') == 'pending_pusat' ? 'selected' : '' }}>Pending
                            Pusat</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Selesai</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>

                    <div class="pm-filter-right">
                        <button id="applyFilter" class="pm-filter-apply">
                            <i class="fas fa-filter"></i> Terapkan
                        </button>
                    </div>

                </form>
            </div>



            <div class="table-responsive">
                <table class="table table-hover align-middle mt-3">

                    <thead>
                        <tr>
                            <th>Segment</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($segments as $segment)
                            @foreach ($segment->schedules as $schedule)
                                <tr>

                                    <td>{{ $segment->nama_segment }}</td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($schedule->planned_date)->format('d F Y') }}
                                    </td>

                                    <td>

                                        @if (!$schedule->inspeksiHeader && $schedule->status === 'rejected')
                                            <span class="badge bg-danger">Rejected (Auto)</span>
                                        @elseif (!$schedule->inspeksiHeader)
                                            <span class="badge bg-success">Belum Dikerjakan</span>
                                        @elseif ($schedule->inspeksiHeader->status_workflow == 'draft')
                                            <span class="badge bg-secondary">Draft</span>
                                        @elseif ($schedule->inspeksiHeader->status_workflow == 'pending_ro')
                                            <span class="badge bg-info">Pending RO</span>
                                        @elseif ($schedule->inspeksiHeader->status_workflow == 'pending_pusat')
                                            <span class="badge bg-primary">Pending Pusat</span>
                                        @elseif ($schedule->inspeksiHeader->status_workflow == 'approved')
                                            <span class="badge bg-success">Selesai</span>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif

                                    </td>

                                    <td class="aksi-cell">

                                        @php
                                            $inspeksi = $schedule->inspeksiHeader;
                                            $canManageDraft =
                                                $inspeksi &&
                                                $inspeksi->status_workflow === 'draft' &&
                                                ((string) $inspeksi->prepared_by === (string) auth()->id() ||
                                                    (string) $inspeksi->prepared_by ===
                                                        (string) auth()->user()->username);
                                        @endphp

                                        @if ($schedule->inspeksiHeader)
                                            <div class="action-group">
                                                <button class="action-btn btn-view view-report"
                                                    title="Detail"
                                                    data-id="{{ $schedule->inspeksiHeader->id }}"
                                                    data-status="{{ $schedule->inspeksiHeader->status_workflow }}"
                                                    data-can-submit="{{ $canManageDraft ? 1 : 0 }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                @if ($canManageDraft)
                                                    <form action="{{ route('inspeksi.submit', $inspeksi->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="action-btn btn-send"
                                                            title="Kirim ke Kepala RO"
                                                            onclick="return confirm('Kirim draft ini ke Kepala RO?')">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </button>
                                                    </form>

                                                    <button type="button" class="action-btn btn-edit"
                                                        title="Edit Draft"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editDraftModal"
                                                        data-edit-url="{{ route('tasks.show', $schedule->id) }}?draft_id={{ $inspeksi->id }}&embedded=1">
                                                        <i class="fas fa-pen"></i>
                                                    </button>

                                                    <form action="{{ route('inspeksi.destroy-draft', $inspeksi->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="action-btn btn-delete"
                                                            title="Hapus Draft"
                                                            onclick="return confirm('Yakin ingin menghapus draft ini?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @elseif ($schedule->status === 'rejected')
                                            <span class="text-muted">Ditolak otomatis (terlewat)</span>
                                        @else
                                            <span class="text-muted">Belum ada laporan</span>
                                        @endif

                                    </td>

                                </tr>
                            @endforeach
                        @endforeach

                    </tbody>
                </table>

            </div>


            <!-- MODAL -->
            <div class="modal fade" id="reportModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">Detail Laporan Inspeksi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body" id="reportContent">
                            Loading...
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-success" id="btnKirimRO">
                                Kirim ke Kepala RO
                            </button>

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Tutup
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal fade" id="editDraftModal" tabindex="-1">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title">Edit Draft - Form Inspeksi Jaringan Fiber Optik</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0">
                            <iframe id="editDraftIframe" class="edit-iframe" src="about:blank"></iframe>
                        </div>
                    </div>
                </div>
            </div>

            <form id="submitDraftForm" method="POST" class="d-none">
                @csrf
            </form>

        </div>
    @endsection


    @push('scripts')
        <script>
            let currentId = null;
            let currentCanSubmit = false;

            $(document).on('click', '.view-report', function() {

                let id = $(this).data('id');
                let status = $(this).data('status');
                let canSubmit = Number($(this).data('can-submit')) === 1;

                currentId = id;
                currentCanSubmit = canSubmit;

                let modal = new bootstrap.Modal(document.getElementById('reportModal'));
                modal.show();

                $('#reportContent').html('Loading...');

                if (status === 'pending_ro') {
                    $('#btnKirimRO')
                        .prop('disabled', true)
                        .text('Sudah Dikirim ke RO');

                } else if (status === 'pending_pusat') {
                    $('#btnKirimRO')
                        .prop('disabled', true)
                        .text('Sudah Dikirim ke Pusat');

                } else if (status === 'approved') {
                    $('#btnKirimRO')
                        .prop('disabled', true)
                        .text('Laporan Disetujui');

                } else if (status === 'draft' && !canSubmit) {
                    $('#btnKirimRO')
                        .prop('disabled', true)
                        .text('Draft Milik Teknisi Lain');
                } else {
                    $('#btnKirimRO')
                        .prop('disabled', false)
                        .text('Kirim ke Kepala RO');
                }

                // FIX AJAX
                $.get('/report/modal/' + id)
                    .done(function(data) {
                        $('#reportContent').html(data);
                    })
                    .fail(function(err) {
                        console.log(err);
                        $('#reportContent').html('<p class="text-danger">Gagal load data</p>');
                    });

            });

            $('#btnKirimRO').on('click', function() {
                if (!currentId || !currentCanSubmit) {
                    return;
                }

                if (!confirm('Kirim draft ini ke Kepala RO?')) {
                    return;
                }

                const form = document.getElementById('submitDraftForm');
                form.action = `/inspeksi/submit/${currentId}`;
                form.submit();
            });

            $(document).on('click', '.btn-edit', function() {
                const editUrl = $(this).data('edit-url');
                if (!editUrl) return;

                $('#editDraftIframe').attr('src', editUrl);
            });

            $('#editDraftModal').on('hidden.bs.modal', function() {
                $('#editDraftIframe').attr('src', 'about:blank');
            });
        </script>
    @endpush
