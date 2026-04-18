<div class="inspection-wrapper">

    <!-- HEADER -->
    <div class="card shadow-sm mb-4">
        <div class="card-body text-center">
            <h4 class="fw-bold mb-1">Data Inspeksi</h4>
            <small class="text-muted">Laporan Patroli Jaringan Fiber Optik</small>
        </div>
    </div>


    <!-- INFORMASI INSPEKSI -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-semibold">
            Informasi Inspeksi
        </div>

        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">

                <tr>
                    <th style="width:250px">Segment</th>
                    <td>{{ $report->segment_inspeksi }}</td>
                </tr>

                <tr>
                    <th>Tanggal Inspeksi</th>
                    <td>{{ \Carbon\Carbon::parse($report->tanggal_inspeksi)->format('d F Y') }}</td>
                </tr>

                <tr>
                    <th>Pelaksana</th>
                    <td>{{ $report->nama_pelaksana ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Driver</th>
                    <td>{{ $report->driver ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Cara Patroli</th>
                    <td>{{ $report->cara_patroli }}</td>
                </tr>

            </table>
        </div>
    </div>


    <!-- KONDISI JARINGAN -->
    <div class="card shadow-sm mb-4">

        <div class="card-header bg-light fw-semibold">
            Kondisi Jaringan
        </div>

        <div class="card-body p-0">

            <table class="table table-hover table-bordered align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th style="width:200px">Objek</th>
                        <th class="text-center" style="width:80px">Ya</th>
                        <th class="text-center" style="width:80px">Tidak</th>
                        <th>Catatan</th>
                    </tr>
                </thead>

                <tbody>

                    @if ($report->details && count($report->details) > 0)
                        @foreach ($report->details as $detail)
                            @php

                                $status = json_decode($detail->status, true);

                                // ambil nilai dari berbagai kemungkinan key
                                $nilai = strtolower(
                                    $status['status'] ?? ($status['posisi'] ?? ($status['kondisi'] ?? '')),
                                );

                                $hasil = null;

                                if ($nilai != '') {
                                    if (in_array($nilai, ['ya', 'ada', 'rusak', 'tidak_aman', 'miring'])) {
                                        $hasil = 'ya';
                                    } else {
                                        $hasil = 'tidak';
                                    }
                                }
                            @endphp

                            <tr>

                                <td>
                                    {{ ucfirst(str_replace('_', ' ', $detail->objek)) }}
                                </td>

                                <td class="text-center">
                                    @if ($hasil === 'ya')
                                        ✔
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if ($hasil === 'tidak')
                                        ✔
                                    @endif
                                </td>
                                <td>
                                    {{ $detail->catatan ?? '-' }}
                                </td>

                            </tr>
                        @endforeach
                        {{-- ================= KONDISI UMUM (4 ITEM) ================= --}}
                        @if ($report->kondisiUmum)
                            <tr>
                                <td>Marker Post</td>

                                <td class="text-center">
                                    {{ $report->kondisiUmum->marker_post == 'rusak' ? '✔' : '' }}
                                </td>

                                <td class="text-center">
                                    {{ $report->kondisiUmum->marker_post == 'baik' ? '✔' : '' }}
                                </td>

                                <td>
                                    {{ $report->kondisiUmum->catatan_marker_post ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <td>Hand Hole</td>

                                <td class="text-center">
                                    {{ $report->kondisiUmum->hand_hole == 'rusak' ? '✔' : '' }}
                                </td>

                                <td class="text-center">
                                    {{ $report->kondisiUmum->hand_hole == 'baik' ? '✔' : '' }}
                                </td>

                                <td>
                                    {{ $report->kondisiUmum->catatan_hand_hole ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <td>Aksesoris KU</td>

                                <td class="text-center">
                                    {{ $report->kondisiUmum->aksesoris_ku == 'rusak' ? '✔' : '' }}
                                </td>

                                <td class="text-center">
                                    {{ $report->kondisiUmum->aksesoris_ku == 'baik' ? '✔' : '' }}
                                </td>

                                <td>
                                    {{ $report->kondisiUmum->catatan_aksesoris_ku ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <td>JC / ODP</td>

                                <td class="text-center">
                                    {{ $report->kondisiUmum->jc_odp == 'rusak' ? '✔' : '' }}
                                </td>

                                <td class="text-center">
                                    {{ $report->kondisiUmum->jc_odp == 'baik' ? '✔' : '' }}
                                </td>

                                <td>
                                    {{ $report->kondisiUmum->catatan_jc_odp ?? '-' }}
                                </td>
                            </tr>
                        @endif
                    @else
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data</td>
                        </tr>
                    @endif

                </tbody>

            </table>

        </div>

    </div>
    <!-- ===================== BUKTI PM ===================== -->
    <div class="card shadow-sm mb-4">

        <div class="card-header bg-light fw-semibold">
            Bukti PM (Foto Lapangan)
        </div>

        <div class="card-body">

            @if ($report->images && count($report->images) > 0)

                <div class="d-flex flex-wrap">

                    @foreach ($report->images as $img)
                        <img src="{{ asset('storage/' . $img->image_path) }}" width="150"
                            class="me-2 mb-2 rounded shadow-sm">
                    @endforeach

                </div>
            @else
                <p class="text-muted">Tidak ada gambar</p>
            @endif

        </div>

    </div>

    <!-- TANDA TANGAN -->
    <div class="card shadow-sm">

        <div class="card-header bg-light fw-semibold">
            Tanda Tangan
        </div>

        <div class="card-body">

            <div class="row text-center">

                <!-- Pelaksana -->
                <div class="row text-center mt-3">

                    <!-- Teknisi -->
                    <div class="col-md-6">
                        <p class="fw-semibold">Pelaksana</p>

                        @if ($report->prepared_signature)
                            <img src="{{ $report->prepared_signature }}" style="height:80px;">
                        @endif

                        <p class="mt-2">
                            {{ $report->nama_pelaksana ?? '-' }}
                        </p>
                    </div>

                    <!-- Kepala RO -->
                    <div class="col-md-6">
                        <p class="fw-semibold">Kepala RO</p>

                        @if ($report->approved_signature)
                            <img src="{{ $report->approved_signature }}" style="height:80px;">
                        @else
                            <p class="text-muted">Belum ditandatangani</p>
                        @endif

                        <p class="mt-2">
                            {{ $report->approver->name ?? 'Kepala RO' }}
                        </p>
                    </div>

                </div>




            </div>

        </div>

    </div>

    @if (auth()->user()->role == 'kepala_ro' && $report->status_workflow == 'pending_ro')
        <hr>

        <h5>Keputusan Kepala RO</h5>

        <canvas id="canvas_ro" width="320" height="150" style="border:1px dashed #999;"></canvas>

        <br>

        <button type="button" onclick="clearRO()" class="btn btn-sm btn-secondary mt-2">
            Clear
        </button>

        <br><br>

        <form method="POST" action="{{ route('reports.approve.ro', $report->id) }}">
            @csrf

            <input type="hidden" name="signature_ro" id="signature_ro">

            <button class="btn btn-success">
                Approve
            </button>

        </form>

        <br>

        <form method="POST" action="{{ route('reports.reject.ro', $report->id) }}">
            @csrf

            <textarea name="reject_note" class="form-control mb-2" placeholder="Alasan reject" required></textarea>

            <button class="btn btn-danger">
                Reject
            </button>

        </form>
    @endif

    @if (auth()->user()->role == 'pusat' && $report->status_workflow == 'pending_pusat')
        <hr>

        <h5>Keputusan Pusat</h5>

        <form method="POST" action="{{ route('reports.approve.pusat', $report->id) }}">
            @csrf
            <button class="btn btn-success">
                Approve
            </button>
        </form>

        <br>

        <form method="POST" action="{{ route('reports.reject.pusat', $report->id) }}">
            @csrf

            <textarea name="reject_note" class="form-control mb-2" placeholder="Alasan reject" required></textarea>

            <button class="btn btn-danger">
                Reject
            </button>

        </form>
    @endif

</div>



</div>
<script>
    const canvas = document.getElementById("canvas_ro");

    if (canvas) {

        const ctx = canvas.getContext("2d");
        let drawing = false;

        // ===== EVENT MOUSE =====
        canvas.addEventListener("mousedown", start);
        canvas.addEventListener("mouseup", stop);
        canvas.addEventListener("mousemove", draw);

        // ===== EVENT TOUCH (HP) =====
        canvas.addEventListener("touchstart", startTouch);
        canvas.addEventListener("touchend", stop);
        canvas.addEventListener("touchmove", drawTouch);

        function start(e) {
            drawing = true;
            draw(e);
        }

        function startTouch(e) {
            drawing = true;
            drawTouch(e);
        }

        function stop() {
            drawing = false;
            ctx.beginPath();
            saveSignature();
        }

        function draw(e) {
            if (!drawing) return;

            const rect = canvas.getBoundingClientRect();

            ctx.lineWidth = 2;
            ctx.lineCap = "round";
            ctx.strokeStyle = "#000";

            ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
        }

        function drawTouch(e) {
            if (!drawing) return;

            e.preventDefault();

            const rect = canvas.getBoundingClientRect();
            const touch = e.touches[0];

            ctx.lineWidth = 2;
            ctx.lineCap = "round";
            ctx.strokeStyle = "#000";

            ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
        }

        function saveSignature() {
            document.getElementById("signature_ro").value = canvas.toDataURL();
        }

        // ===== CEK KOSONG =====
        function isCanvasEmpty(canvas) {
            const blank = document.createElement("canvas");
            blank.width = canvas.width;
            blank.height = canvas.height;
            return canvas.toDataURL() === blank.toDataURL();
        }

        // ===== PAKSA SIMPAN SAAT SUBMIT =====
        const form = canvas.closest("form");
        if (form) {
            form.addEventListener("submit", function(e) {

                if (isCanvasEmpty(canvas)) {
                    e.preventDefault();
                    alert("Tanda tangan wajib diisi!");
                    return;
                }

                document.getElementById("signature_ro").value = canvas.toDataURL();
            });
        }

    }

    // ===== CLEAR =====
    function clearRO() {
        const canvas = document.getElementById("canvas_ro");
        const ctx = canvas.getContext("2d");

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // reset hidden input
        document.getElementById("signature_ro").value = "";
    }
</script>
