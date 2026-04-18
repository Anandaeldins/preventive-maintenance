@extends('layouts.bar')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Jadwal PM Saya</h5>
                        @if (in_array(Auth::user()->role, ['admin', 'teknisi']))
                            <a href="{{ route('pm-schedules.create') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus"></i> Buat Jadwal Baru
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        <div class="pm-filter-bar">

                            <div class="pm-filter-left">

                                <select id="filterSegment" class="pm-filter-select">
                                    <option value="">Segment</option>
                                    @foreach ($segments as $s)
                                        <option value="{{ $s->nama_segment }}">
                                            {{ $s->nama_segment }}
                                        </option>
                                    @endforeach
                                </select>

                                <select id="filterPriority" class="pm-filter-select">
                                    <option value="">Prioritas</option>
                                    <option value="KRITIS">KRITIS</option>
                                    <option value="SEDANG">SEDANG</option>
                                    <option value="RENDAH">RENDAH</option>
                                </select>

                                {{-- <select id="sortDate" class="pm-filter-select">
                                    <option value="">Sort by Date</option>
                                    <option value="asc">Tanggal Terdekat</option>
                                    <option value="desc">Tanggal Terjauh</option>
                                </select> --}}

                                <select id="filterPIC" class="pm-filter-select">
                                    <option value="">PIC</option>

                                    @foreach ($teknisis as $t)
                                        <option value="{{ $t->username }}">
                                            {{ $t->username }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>

                            <div class="pm-filter-right">
                                <button id="applyFilter" class="pm-filter-apply">
                                    <i class="fas fa-filter"></i> Terapkan
                                </button>
                            </div>

                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mt-3 custom-table" id="schedulesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Segment</th>
                                        <th>Tanggal Planned</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Dibuat Oleh</th>

                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($schedules as $index => $group)
                                        @php
                                            $first = $group->first(); // ambil data utama untuk kolom umum
                                        @endphp
                                        <tr data-created="{{ $first->created_at }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                {{ $first->segment->nama_segment ?? '-' }}
                                            </td>
                                            <td data-date="{{ $first->planned_date }}">
                                                {{ \Carbon\Carbon::parse($first->planned_date)->translatedFormat('F Y') }}
                                            </td>
                                            <td>
                                                @if ($first->priority === 'KRITIS')
                                                    <span class="badge bg-danger">KRITIS</span>
                                                @elseif($first->priority === 'SEDANG')
                                                    <span class="badge bg-warning text-dark">SEDANG</span>
                                                @else
                                                    <span class="badge bg-success">RENDAH</span>
                                                @endif
                                            </td>
                                            <td>
                                                @switch($first->status)
                                                    @case('draft')
                                                        <span class="badge bg-secondary">Draft</span>
                                                    @break

                                                    @case('pending')
                                                        <span class="badge bg-info">Menunggu Approval</span>
                                                    @break

                                                    @case('pending_pusat')
                                                        <span class="badge bg-primary">Menunggu Pusat</span>
                                                    @break

                                                    @case('approved')
                                                        <span class="badge bg-success">Disetujui</span>
                                                    @break

                                                    @case('rejected')
                                                        <span class="badge bg-danger">Ditolak</span>
                                                    @break

                                                    @default
                                                        <span class="badge bg-secondary">{{ $first->status }}</span>
                                                @endswitch
                                            </td>

                                            <td>{{ $first->creator->username ?? '-' }}</td>

                                            <td>
                                                <div class="action-group">

                                                    <a href="#" class="action-btn btn-view" data-bs-toggle="modal"
                                                        data-bs-target="#viewModal{{ $first->id }}" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    @if ($first->status === 'draft' && Auth::id() === $first->created_by)
                                                        <form action="{{ route('pm-schedules.submit', $first->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="action-btn btn-send"
                                                                title="Kirim"
                                                                onclick="return confirm('Kirim jadwal ini untuk approval?')">
                                                                <i class="fas fa-paper-plane"></i>
                                                            </button>
                                                        </form>

                                                        <a href="#" class="action-btn btn-edit" data-bs-toggle="modal"
                                                            data-bs-target="#editModal{{ $first->id }}">
                                                            <i class="fas fa-pen"></i>
                                                        </a>

                                                        <form action="{{ route('pm-schedules.destroy', $first->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="action-btn btn-delete"
                                                                title="Hapus"
                                                                onclick="return confirm('Yakin hapus jadwal ini?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                </div>
                                            </td>
                                        </tr>


                                        <!-- MODAL DETAIL -->
                                        <div class="modal fade" id="viewModal{{ $first->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">

                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">Detail Jadwal PM</h5>
                                                        <button class="btn-close btn-close-white"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <div class="modal-body">


                                                        <p>
                                                            <b>Segment:</b>
                                                            {{ $first->segment->kode_segment ?? '-' }}
                                                            -
                                                            {{ $first->segment->nama_segment ?? '' }}
                                                        </p>
                                                        <p><b>Bulan:</b>
                                                            {{ \Carbon\Carbon::parse($first->planned_date)->translatedFormat('F Y') }}
                                                        </p>

                                                        <p><b>Prioritas:</b> {{ $first->priority }}</p>

                                                        <p><b>Dibuat oleh:</b>
                                                            {{ $first->creator->username ?? '-' }}
                                                        </p>


                                                        <p><b>Catatan:</b> {{ $first->notes ?? '-' }}</p>

                                                        <hr>

                                                        <b>Tanggal Jadwal</b>

                                                        <div class="mt-2">

                                                            @foreach ($group->sortBy('planned_date') as $item)
                                                                <span class="badge bg-primary me-1 mb-1">
                                                                    {{ \Carbon\Carbon::parse($item->planned_date)->format('d') }}
                                                                </span>
                                                            @endforeach

                                                        </div>



                                                        </ul>

                                                        <hr>

                                                        <hr>

                                                        <b>Tanda Tangan Teknisi</b><br>

                                                        @if ($first->signature_teknisi)
                                                            <img src="{{ asset('storage/' . $first->signature_teknisi) }}"
                                                                style="max-height:120px">
                                                        @else
                                                            <span class="text-muted">Belum ada tanda tangan teknisi</span>
                                                        @endif


                                                        <hr>

                                                        <b>Tanda Tangan Kepala RO</b><br>

                                                        @if ($first->status === 'approved' && $first->signature_ro)
                                                            <img src="{{ asset('storage/' . $first->signature_ro) }}"
                                                                style="max-height:120px">
                                                        @elseif($first->status === 'approved')
                                                            <span class="text-warning">Jadwal sudah disetujui, tetapi tanda
                                                                tangan belum tersedia</span>
                                                        @else
                                                            <span class="text-muted">Belum ada tanda tangan Kepala
                                                                RO</span>
                                                        @endif

                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-secondary" data-bs-dismiss="modal">
                                                            Tutup
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <!-- MODAL EDIT -->
                                        <div class="modal fade" id="editModal{{ $first->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">

                                                    <div class="modal-header bg-warning">
                                                        <h5 class="modal-title">Edit Jadwal PM</h5>
                                                        <button class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <form method="POST"
                                                        action="{{ route('pm-schedules.update', $first->id) }}">
                                                        @csrf
                                                        @method('PUT')

                                                        <div class="modal-body">

                                                            <label>Segment</label>

                                                            <select name="segment_id" class="form-control">

                                                                @foreach ($segments as $segment)
                                                                    <option value="{{ $segment->id }}"
                                                                        @if ($first->segment_id == $segment->id) selected @endif>

                                                                        {{ $segment->kode_segment }} -
                                                                        {{ $segment->nama_segment }}

                                                                    </option>
                                                                @endforeach

                                                            </select>



                                                            <label class="mt-3">Tanggal Jadwal</label>

                                                            <div id="editCalendar{{ $first->id }}"
                                                                class="calendar-grid"></div>

                                                            <input type="hidden" name="planned_date"
                                                                id="edit_dates{{ $first->id }}"
                                                                value="{{ $group->pluck('planned_date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))->implode(',') }}">

                                                            <label class="mt-3">Teknisi 1</label>

                                                            <select name="teknisi_1" class="form-control">

                                                                @foreach ($teknisis as $t)
                                                                    <option value="{{ $t->id }}"
                                                                        @if ($first->teknisi_1 == $t->id) selected @endif>

                                                                        {{ $t->username }}

                                                                    </option>
                                                                @endforeach

                                                            </select>


                                                            {{-- <label class="mt-3">Teknisi 2</label>

                                                            <select name="teknisi_2" class="form-control">

                                                                <option value="">-- pilih --</option>

                                                                @foreach ($teknisis as $t)
                                                                    <option value="{{ $t->id }}"
                                                                        @if ($first->teknisi_2 == $t->id) selected @endif>

                                                                        {{ $t->username }}

                                                                    </option>
                                                                @endforeach

                                                            </select> --}}


                                                            <label class="mt-3">Catatan</label>

                                                            <textarea name="notes" class="form-control">{{ $first->notes }}</textarea>


                                                            <label class="mt-3">Tanda Tangan Teknisi</label>

                                                            <canvas id="editSignature{{ $first->id }}" width="300"
                                                                height="150"
                                                                style="border:2px solid #000;border-radius:10px;"></canvas>

                                                            <input type="hidden" name="signature_teknisi"
                                                                id="signature_input{{ $first->id }}">

                                                        </div>

                                                        <div class="modal-footer">

                                                            <button class="btn btn-secondary" data-bs-dismiss="modal">
                                                                Batal
                                                            </button>

                                                            <button class="btn btn-primary">
                                                                Update Jadwal
                                                            </button>

                                                        </div>

                                                    </form>

                                                </div>
                                            </div>
                                        </div>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                                    Tidak ada jadwal PM
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    @push('style')
        <style>
            .modal-content {
                border-radius: 16px;
                border: none;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            }

            .modal-header {
                border-bottom: none;
            }

            .modal-footer {
                border-top: none;
            }

            .custom-table {
                border-collapse: separate;
                border-spacing: 0 10px;
            }

            .custom-table thead th {
                font-size: 12px;
                text-transform: uppercase;
                color: #64748b;
                border: none;
            }

            .custom-table tbody tr {
                background: #ffffff;
                border-radius: 12px;
                transition: 0.2s;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
            }

            .custom-table tbody tr:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08);
            }

            .custom-table td {
                padding: 16px;
                border: none;
            }

            .badge {
                padding: 6px 14px;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 500;
            }

            .badge.bg-danger {
                background: #fee2e2 !important;
                color: #dc2626 !important;
            }

            .badge.bg-warning {
                background: #fef9c3 !important;
                color: #ca8a04 !important;
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

            .card {
                border: none;
                border-radius: 18px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
                overflow: hidden;
            }

            .card-header {
                background: linear-gradient(135deg, #4A90E2, #2563eb) !important;
                padding: 18px 24px;
                font-size: 18px;
                font-weight: 600;
            }

            .card-header h5 {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .pm-filter-bar {
                background: #ffffff;
                border-radius: 14px;
                padding: 16px 20px;
                margin-bottom: 20px;

                display: flex;
                justify-content: space-between;
                align-items: center;

                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }

            .pm-filter-select {
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                padding: 8px 12px;
                background: #f8fafc;
                transition: 0.2s;
            }

            .pm-filter-select:focus {
                border-color: #3b82f6;
                background: white;
                box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
            }

            .pm-filter-apply {
                background: linear-gradient(135deg, #4A90E2, #2563eb);
                border: none;
                border-radius: 10px;
                padding: 8px 18px;
                color: white;
                font-weight: 500;
                transition: 0.2s;
            }

            .pm-filter-apply:hover {
                transform: translateY(-1px);
                box-shadow: 0 6px 14px rgba(37, 99, 235, 0.3);
            }

            .pm-filter-select {
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                padding: 8px 12px;
                background: #f8fafc;
                transition: 0.2s;
            }

            .pm-filter-select:focus {
                border-color: #3b82f6;
                background: white;
                box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
            }

            .pm-filter-apply {
                background: linear-gradient(135deg, #4A90E2, #2563eb);
                border: none;
                border-radius: 10px;
                padding: 8px 18px;
                color: white;
                font-weight: 500;
                transition: 0.2s;
            }

            .pm-filter-apply:hover {
                transform: translateY(-1px);
                box-shadow: 0 6px 14px rgba(37, 99, 235, 0.3);
            }

            .pm-filter-left {
                display: flex;
                gap: 12px;
                flex-wrap: wrap;
            }

            .pm-filter-btn {
                background: white;
                border: 1px solid #d1d5db;
                border-radius: 8px;
                padding: 7px 14px;
                font-size: 14px;
                transition: .2s;
            }

            .pm-filter-btn:hover {
                background: #2563eb;
                color: white;
            }

            .pm-filter-select {
                border: 1px solid #d1d5db;
                border-radius: 8px;
                padding: 7px 12px;
                font-size: 14px;
                background: white;
            }

            .pm-filter-apply {
                background: #2563eb;
                color: white;
                border: none;
                border-radius: 8px;
                padding: 8px 18px;
                font-weight: 500;
            }

            .pm-filter-apply:hover {
                background: #1e40af;
            }



            /* ================= ACTION BUTTONS ================= */

            .action-group {
                display: flex;
                justify-content: center;
                gap: 8px;
            }

            .action-btn {
                width: 38px;
                height: 38px;
                border-radius: 12px;
                border: none;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
                color: #fff;
                cursor: pointer;
                transition: all .25s ease;
                box-shadow: 0 4px 10px rgba(0, 0, 0, .12);
                transition: 0.2s;
            }

            .action-btn:hover {
                transform: scale(1.08);
                box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
            }

            .action-btn i {
                pointer-events: none;
            }

            /* VIEW */
            .btn-view {
                background: #06b6d4;
            }

            .btn-view:hover {
                background: #0891b2;
                transform: translateY(-2px);
            }

            /* SEND */
            .btn-send {
                background: #2563eb;
            }

            .btn-send:hover {
                background: #1d4ed8;
                transform: translateY(-2px);
            }

            /* EDIT */
            .btn-edit {
                background: #facc15;
                color: #000;
            }

            .btn-edit:hover {
                background: #eab308;
                transform: translateY(-2px);
            }

            /* DELETE */
            .btn-delete {
                background: #ef4444;
            }

            .btn-delete:hover {
                background: #dc2626;
                transform: translateY(-2px);
            }


            .calendar-grid {
                display: grid;
                grid-template-columns: repeat(7, 45px);
                gap: 8px;
                margin-top: 10px;
            }

            .calendar-day {
                width: 45px;
                height: 45px;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 2px solid #ccc;
                border-radius: 10px;
                cursor: pointer;
                font-weight: 600;
            }

            .calendar-day.weekend {
                background: #ff4d4d;
                color: white;
                border: none;
            }

            .calendar-day.selected {
                background: #16a34a;
                color: white;
                border: none;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            }
        </style>
    @endpush
    @push('scripts')
        <script>
            // ================= NOMOR OTOMATIS =================
            function updateRowNumber() {
                let rows = document.querySelectorAll("#schedulesTable tbody tr");

                let index = 1;

                rows.forEach(row => {
                    if (row.style.display !== "none") {
                        row.children[0].innerText = index++;
                    }
                });
            }


            // ================= SORT SAAT LOAD =================
            document.addEventListener("DOMContentLoaded", function() {

                let tbody = document.querySelector("#schedulesTable tbody");
                let rows = Array.from(tbody.querySelectorAll("tr"));

                rows.sort((a, b) => {
                    let dateA = new Date(a.dataset.created);
                    let dateB = new Date(b.dataset.created);

                    return dateB - dateA; // terbaru di atas
                });

                rows.forEach(row => tbody.appendChild(row));

                updateRowNumber(); // 🔥 nomor ikut rapi
            });


            // ================= FILTER + SORT =================
            document.getElementById("applyFilter").addEventListener("click", function() {

                let priority = document.getElementById("filterPriority").value;
                let pic = document.getElementById("filterPIC").value;
                let segment = document.getElementById("filterSegment").value;

                let tbody = document.querySelector("#schedulesTable tbody");
                let rows = Array.from(tbody.querySelectorAll("tr"));

                // ===== FILTER =====
                rows.forEach(row => {

                    let rowPriority = row.children[3].innerText.trim();
                    let rowPIC = row.children[6].innerText.trim();
                    let rowSegment = row.children[1].innerText.trim();

                    let show = true;

                    if (priority && !rowPriority.includes(priority)) show = false;
                    if (pic && rowPIC !== pic) show = false;
                    if (segment && rowSegment !== segment) show = false;

                    row.style.display = show ? "" : "none";
                });

                // ===== SORT TERBARU =====
                let visibleRows = rows.filter(row => row.style.display !== "none");

                visibleRows.sort((a, b) => {
                    let dateA = new Date(a.dataset.created);
                    let dateB = new Date(b.dataset.created);

                    return dateB - dateA;
                });

                visibleRows.forEach(row => tbody.appendChild(row));

                updateRowNumber(); // 🔥 nomor ikut update
            });
            // SORTING


            document.querySelectorAll("canvas[id^='editSignature']").forEach(canvas => {

                let ctx = canvas.getContext("2d");
                let drawing = false;

                canvas.addEventListener("mousedown", () => drawing = true);

                canvas.addEventListener("mouseup", () => {
                    drawing = false;
                    ctx.beginPath();

                    let id = canvas.id.replace("editSignature", "");
                    document.getElementById("signature_input" + id).value =
                        canvas.toDataURL("image/png");
                });

                canvas.addEventListener("mousemove", e => {

                    if (!drawing) return;

                    let rect = canvas.getBoundingClientRect();

                    let x = e.clientX - rect.left;
                    let y = e.clientY - rect.top;

                    ctx.lineWidth = 2;
                    ctx.lineCap = "round";

                    ctx.lineTo(x, y);
                    ctx.stroke();

                    ctx.beginPath();
                    ctx.moveTo(x, y);

                });

            });
            document.querySelectorAll("[id^='editCalendar']").forEach(cal => {

                let id = cal.id.replace("editCalendar", "");
                let hidden = document.getElementById("edit_dates" + id);

                let dates = hidden.value.split(",").filter(d => d != "");

                let year = dates.length ? dates[0].split("-")[0] : new Date().getFullYear();
                let month = dates.length ? dates[0].split("-")[1] : (new Date().getMonth() + 1);

                let daysInMonth = new Date(year, month, 0).getDate();

                function updateHidden() {

                    hidden.value = dates.join(",");

                }

                for (let d = 1; d <= daysInMonth; d++) {

                    let dateStr = year + "-" + String(month).padStart(2, '0') + "-" + String(d).padStart(2, '0');

                    let day = new Date(year, month - 1, d).getDay();

                    let div = document.createElement("div");

                    div.className = "calendar-day";

                    div.innerText = d;

                    if (day === 0 || day === 6) {
                        div.classList.add("weekend");
                    }

                    if (dates.includes(dateStr)) {
                        div.classList.add("selected");
                    }

                    div.onclick = function() {

                        if (dates.includes(dateStr)) {

                            dates = dates.filter(x => x !== dateStr);
                            div.classList.remove("selected");

                        } else {

                            dates.push(dateStr);
                            div.classList.add("selected");

                        }

                        updateHidden();

                    }

                    cal.appendChild(div);

                }

            });
        </script>
    @endpush
