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

        /* FILTER BAR */
        .pm-filter-bar {
            background: var(--card);
            border-radius: 14px;
            padding: 16px 18px;
            margin-bottom: 20px;

            display: flex;
            justify-content: space-between;
            align-items: center;

            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.1);
        }

        .pm-filter-left {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .pm-filter-select {
            border: 1px solid #dbeafe;
            border-radius: 10px;
            padding: 8px 12px;
            background: white;
        }

        /* BUTTON */
        .pm-filter-apply {
            background: linear-gradient(135deg, #60a5fa, #2563eb);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 8px 18px;
            font-weight: 600;
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

        #pendingTable tr:hover {
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

        .badge-modern.success {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge-modern.danger {
            background: #fee2e2;
            color: #dc2626;
        }

        /* BUTTON ACTION */
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
                <h4>Pending Schedules</h4>
                <p>Daftar jadwal yang menunggu approval</p>
            </div>

            {{-- ALERT --}}
            @if (session('success'))
                <div class="alert-modern success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert-modern error">
                    <strong>Terjadi Kesalahan:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- FILTER --}}
            <div class="pm-filter-bar">
                <div class="pm-filter-left">

                    <select id="filterPriority" class="pm-filter-select">
                        <option value="">Prioritas</option>
                        <option value="KRITIS">KRITIS</option>
                        <option value="SEDANG">SEDANG</option>
                        <option value="RENDAH">RENDAH</option>
                    </select>

                    <select id="sortDate" class="pm-filter-select">
                        <option value="">Sort by Date</option>
                        <option value="asc">Tanggal Terdekat</option>
                        <option value="desc">Tanggal Terjauh</option>
                    </select>

                    <select id="filterPIC" class="pm-filter-select">
                        <option value="">PIC</option>

                        @foreach ($schedules as $group)
                            @php $first = $group->first(); @endphp
                            <option value="{{ $first->creator->username }}">
                                {{ $first->creator->username }}
                            </option>
                        @endforeach
                    </select>

                </div>

                <div>
                    <button id="applyFilter" class="pm-filter-apply">
                        <i class="fas fa-filter"></i> Terapkan
                    </button>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="table-modern">
                <table id="pendingTable">
                    <thead>
                        <tr>
                            <th>Segment</th>
                            <th>Date</th>
                            <th>Diajukan oleh</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($schedules as $group)
                            @php $first = $group->first(); @endphp

                            <tr>

                                <td>{{ $first->segment->nama_segment }}</td>

                                <td>{{ \Carbon\Carbon::parse($first->planned_date)->translatedFormat('F Y') }}</td>

                                <td>{{ $first->creator->username }}</td>

                                <td>
                                    @if ($first->priority == 'KRITIS')
                                        <span class="badge-modern danger">KRITIS</span>
                                    @elseif ($first->priority == 'SEDANG')
                                        <span class="badge-modern warning">SEDANG</span>
                                    @else
                                        <span class="badge-modern success">RENDAH</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge-modern warning">Pending</span>
                                </td>

                                <td>
                                    <button class="btn btn-sm btn-outline-modern" data-bs-toggle="modal"
                                        data-bs-target="#approveModal{{ $first->id }}">
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

    @foreach ($schedules as $group)
        @php
            $first = $group->first();
        @endphp

        <div class="modal fade" id="approveModal{{ $first->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Detail Jadwal PM</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <p>
                            <b>Segment:</b>
                            {{ $first->segment->kode_segment }} -
                            {{ $first->segment->nama_segment }}
                        </p>

                        <p>
                            <b>Bulan:</b>
                            {{ \Carbon\Carbon::parse($first->planned_date)->translatedFormat('F Y') }}
                        </p>

                        <p><b>Prioritas:</b> {{ $first->priority }}</p>

                        <p><b>Dibuat oleh:</b> {{ $first->creator->username }}</p>

                        <hr>

                        <b>Tanggal Jadwal</b>

                        <div class="mt-2">

                            @foreach ($group->sortBy('planned_date') as $item)
                                <span class="badge bg-primary me-1 mb-1">

                                    {{ \Carbon\Carbon::parse($item->planned_date)->format('d') }}

                                </span>
                            @endforeach

                        </div>

                        <hr>

                        <b>Tanda Tangan Teknisi</b><br>

                        @if ($first->signature_teknisi)
                            <img src="{{ asset('storage/' . $first->signature_teknisi) }}" style="max-height:120px">
                        @endif

                        <hr>

                        <b>Tanda Tangan Kepala RO</b>

                        <canvas id="signature-pad-{{ $first->id }}"
                            style="border:1px solid #ccc;width:100%;height:150px"></canvas>
                    </div>

                    <div class="modal-footer">

                        <form action="{{ route('approval.reject-group') }}" method="POST">
                            @csrf
                            <input type="hidden" name="group_id"
                                value="{{ $first->segment_id }}|{{ date('Y-m', strtotime($first->planned_date)) }}">
                            <button class="btn btn-danger">Reject</button>
                        </form>

                        <form action="{{ route('approval.approve-group') }}" method="POST"
                            onsubmit="saveSignature({{ $first->id }})">

                            @csrf

                            <input type="hidden" name="signature_ro" id="signature-input-{{ $first->id }}">

                            <input type="hidden" name="group_id"
                                value="{{ $first->segment_id }}|{{ date('Y-m', strtotime($first->planned_date)) }}">

                            <button class="btn btn-success">Approve</button>

                        </form>



                        </form>

                    </div>

                </div>
            </div>
        </div>
    @endforeach
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

    <script>
        document.getElementById("applyFilter").addEventListener("click", function() {

            let priority = document.getElementById("filterPriority").value;
            let pic = document.getElementById("filterPIC").value;

            let rows = document.querySelectorAll("#pendingTable tbody tr");

            rows.forEach(row => {

                let rowPriority = row.children[3].innerText.trim();
                let rowPIC = row.children[2].innerText.trim();

                let show = true;

                if (priority && !rowPriority.includes(priority)) {
                    show = false;
                }

                if (pic && rowPIC !== pic) {
                    show = false;
                }

                row.style.display = show ? "" : "none";

            });

        });
        document.addEventListener("DOMContentLoaded", function() {

            let signaturePads = {};

            document.querySelectorAll(".modal").forEach(modal => {

                modal.addEventListener("shown.bs.modal", function() {

                    let canvas = modal.querySelector("canvas");

                    if (!canvas) return;

                    let id = canvas.id.replace('signature-pad-', '');

                    canvas.width = canvas.offsetWidth;
                    canvas.height = 150;

                    signaturePads[id] = new SignaturePad(canvas);

                });

            });

            window.clearSignature = function(id) {
                if (signaturePads[id]) {
                    signaturePads[id].clear();
                }
            }

            window.saveSignature = function(id) {

                if (signaturePads[id] && !signaturePads[id].isEmpty()) {

                    let data = signaturePads[id].toDataURL();

                    document.getElementById('signature-input-' + id).value = data;

                }

            }

        });
    </script>
@endpush
