<!doctype html>

<html lang="id">


<head>
    <meta charset="UTF-8">
    <title>Form Inspeksi Jaringan Fiber Optik</title>

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        :root {
            --blue: #2563eb;
            --blue-soft: #3b82f6;
            --blue-light: #93c5fd;
            --bg-gradient: linear-gradient(135deg, #e0f2fe, #dbeafe, #eff6ff);
            --card: #ffffff;
            --text: #1e293b;
            --muted: #64748b;
        }

        .container-form {

            max-width: 900px;

            margin: auto;

        }

        /* ================= BODY ================= */

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 50px;
            background: var(--bg-gradient);
            color: var(--text);
        }

        /* ================= TITLE ================= */

        h2 {
            text-align: center;
            margin-bottom: 40px;
            font-weight: 700;
            font-size: 28px;
            color: var(--blue);
        }

        h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: var(--blue);
        }

        h4 {
            margin-top: 25px;
            margin-bottom: 10px;
            color: #334155;
        }

        /* ================= SECTION CARD ================= */

        .section {
            background: var(--card);
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 25px;

            box-shadow:
                0 10px 25px rgba(0, 0, 0, 0.05),
                0 2px 6px rgba(0, 0, 0, 0.04);

            transition: all .2s ease;
        }

        .section:hover {
            transform: translateY(-2px);
        }

        /* ================= FORM ================= */

        label {
            font-weight: 600;
            font-size: 13px;
            color: var(--muted);
            display: block;
            margin-bottom: 2px;
        }

        /* ================= INPUT ================= */

        input,
        select,
        textarea {

            width: 100%;
            padding: 10px 12px;

            border-radius: 8px;
            border: 1px solid #e2e8f0;

            font-size: 14px;
            background: #f8fafc;

            transition: all .2s ease;
        }

        textarea {
            resize: vertical;
        }

        /* focus */

        input:focus,
        select:focus,
        textarea:focus {

            outline: none;

            border-color: var(--blue);

            background: white;

            box-shadow:
                0 0 0 2px rgba(37, 99, 235, 0.15);
        }

        /* readonly */

        input[readonly] {
            background: #eef2ff;
            font-weight: 600;
        }

        /* ================= GRID ================= */

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        /* ================= CANVAS ================= */

        canvas {

            border: 1px dashed #94a3b8;

            background: white;

            border-radius: 10px;

            margin-top: 10px;

        }

        /* ================= BUTTON ================= */

        button {

            padding: 10px 18px;

            border: none;

            border-radius: 8px;

            font-weight: 600;

            background: linear-gradient(135deg, #3b82f6, #2563eb);

            color: white;

            cursor: pointer;

            transition: all .2s ease;

            box-shadow: 0 6px 14px rgba(37, 99, 235, 0.25);
        }

        button:hover {

            transform: translateY(-1px);

            box-shadow: 0 10px 18px rgba(37, 99, 235, 0.3);
        }

        /* submit */

        form>button[type="submit"] {

            width: 100%;

            padding: 14px;

            font-size: 16px;

            margin-top: 20px;
        }

        /* ================= HR ================= */

        hr {

            border: none;

            border-top: 1px solid #e2e8f0;

            margin: 25px 0;

        }

        /* ================= SELECT2 ================= */

        .select2-container--default .select2-selection--single {

            height: 40px;

            border-radius: 8px;

            border: 1px solid #e2e8f0;

        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {

            line-height: 38px;

        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {

            height: 38px;
        }

        .form-group {
            grid-template-columns: 140px 1fr;
            gap: 8px;
            margin-bottom: 8px;
        }

        .form-group label {

            margin: 0;

            font-weight: 600;

            font-size: 13px;

        }

        .form-group input,
        .form-group select,
        .form-group textarea {

            width: 100%;

        }

        .btn-toggle {
            width: 100%;
            text-align: left;
            padding: 12px;
            font-weight: 600;
            background: #2599e7;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        #sectionB {
            display: none;
        }

        .row-item {
            display: grid;
            grid-template-columns: 180px 120px 80px 120px 80px 120px 1fr;
            gap: 10px;
            align-items: center;
            margin-bottom: 12px;
            background: #f8fafc;
            padding: 10px;
            border-radius: 10px;
        }

        .row-item label {
            font-weight: 600;
        }

        .row-item span {
            font-size: 12px;
            color: #64748b;
        }

        /* mobile */
        @media(max-width:768px) {
            .row-item {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            grid-template-columns: 1fr;
        }

        .alert {
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 14px;
            font-size: 14px;
            border: 1px solid;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-color: #bbf7d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .preview-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px;
            background: #fff;
        }

        .preview-card img {
            width: 100%;
            height: 110px;
            object-fit: cover;
            border-radius: 8px;
        }

        .signature-preview {
            margin-top: 10px;
        }

        .signature-preview img {
            max-width: 280px;
            max-height: 140px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: #fff;
        }
    </style>
</head>

<body>

    <div class="container-form">

        <div style="max-width:900px;margin:auto"></div>
        <h2>Form Inspeksi Jaringan Fiber Optik</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $isEditDraft = isset($draft) && $draft;
        @endphp

        <form method="POST"
            action="{{ $isEditDraft ? route('inspeksi.update-draft', $draft->id) : route('tasks.store', ['schedule' => $schedule->id]) }}"
            target="{{ request('embedded') ? '_top' : '_self' }}"
            enctype="multipart/form-data">
            @csrf
            @if ($isEditDraft)
                @method('PUT')
            @endif
            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">


            <!-- ===================== A. DATA INSPEKSI ===================== -->


            <div class="section">
                <h3>A. Data Inspeksi</h3>

                <input type="text" value="{{ $schedule->segment->nama_segment }}" readonly>

                <input type="hidden" name="segment_inspeksi" value="{{ $schedule->segment->nama_segment }}">

                <label>Jenis Jalur FO</label>
                <div class="form-group">
                    <input type="text" value="{{ ucfirst(str_replace('_', ' ', $schedule->segment->jalur)) }}"
                        readonly>
                </div>

                <div class="form-group">
                    <input type="hidden" name="jalur_fo" value="{{ $schedule->segment->jalur }}">
                </div>

                <label>Nama Pelaksana</label>
                <div class="form-group">
                    <select name="nama_pelaksana" id="nama_pelaksana" class="select2">
                        <option value="">-- Pilih Teknisi --</option>
                        @foreach ($teknisi as $t)
                            <option value="{{ $t->username }}">{{ $t->username }}</option>
                        @endforeach
                    </select>
                </div>
                <label>Cara Patroli</label>
                <div class="form-group">
                    <select name="cara_patroli" id="cara_patroli">
                        <option value="mobil">Mobil</option>
                        <option value="motor">Motor</option>
                        <option value="jalan_kaki">Jalan Kaki</option>
                        <option value="lainnya">Lain-lain</option>
                    </select>
                </div>

                <label>Driver</label>
                <div class="form-group">
                    <input type="text" name="driver">
                </div>


                <label id="label_cara_patroli_lainnya" style="display:none">
                    Keterangan Lain-lain
                </label>
                <div class="form-group">
                    <input type="text" name="cara_patroli_lainnya" id="cara_patroli_lainnya"
                        placeholder="Isi keterangan cara patroli lain..." style="display:none">

                    <input type="date" name="tanggal_inspeksi" value="{{ $schedule->planned_date->format('Y-m-d') }}"
                        readonly>
                </div>
            </div>
            <hr>

            <!-- ===================== B. KONDISI UMUM ===================== -->
            <div class="section">
                <button type="button" onclick="toggleSection('sectionB')" class="btn-toggle">
                    B. Kondisi Umum Jaringan Fiber Optik ⬇
                </button>

                <div id="sectionB">

                    <div class="row-item">
                        <label>1. Kabel Putus</label>

                        <select name="kabel_putus[status]">
                            <option value="">Pilih</option>
                            <option value="tidak">Tidak</option>
                            <option value="ya">Ya</option>
                        </select>

                        <span>Backup</span>
                        <select name="kabel_putus[backup]">
                            <option value="">Pilih</option>
                            <option value="ada">Ada</option>
                            <option value="tidak">Tidak</option>
                        </select>

                        <span>Dampak</span>
                        <select name="kabel_putus[dampak]">
                            <option value="">Pilih</option>
                            <option value="normal">Normal</option>
                            <option value="sebagian">Sebagian</option>
                            <option value="down">Down</option>
                        </select>

                        <input type="text" name="kondisi[kabel_putus][catatan]" placeholder="Catatan...">
                    </div>




                    <!-- 2. KABEL EXPOSE -->
                    <div class="row-item">
                        <label>2. Kabel Expose</label>

                        <select name="kabel_expose[status]">
                            <option value="">Pilih</option>
                            <option value="tidak">Tidak</option>
                            <option value="ada">Ada</option>
                        </select>

                        <span>Pelindung</span>
                        <select name="kabel_expose[pelindung]">
                            <option value="">Pilih</option>
                            <option value="utuh">Utuh</option>
                            <option value="retak">Retak</option>
                            <option value="rusak">Rusak</option>
                        </select>

                        <span>Lingkungan</span>
                        <select name="kabel_expose[lingkungan]">
                            <option value="">Pilih</option>
                            <option value="aman">Aman</option>
                            <option value="tanah_air">Tanah/Air</option>
                            <option value="beban">Beban</option>
                        </select>

                        <input type="text" name="kondisi[kabel_expose][catatan]" placeholder="Catatan...">
                    </div>


                    <!-- 3. PENYANGGA JEMBATAN -->

                    <div class="row-item">
                        <label>3. Penyangga</label>

                        <select name="penyangga[status]">
                            <option value="">Pilih</option>
                            <option value="baik">Baik</option>
                            <option value="rusak">Rusak</option>
                        </select>

                        <span>Kondisi</span>
                        <select name="penyangga[kondisi]">
                            <option value="">Pilih</option>
                            <option value="karat">Karat</option>
                            <option value="retak">Retak</option>
                            <option value="lepas">Lepas</option>
                        </select>

                        <span>Kabel</span>
                        <select name="penyangga[kabel]">
                            <option value="">Pilih</option>
                            <option value="aman">Aman</option>
                            <option value="menurun">Menurun</option>
                            <option value="tertarik">Tertarik</option>
                        </select>

                        <input type="text" name="kondisi[penyangga][catatan]" placeholder="Catatan...">
                    </div>

                    <!-- 4. TIANG KU -->
                    <div class="row-item">
                        <label>4. Tiang KU</label>

                        <select name="tiang[posisi]">
                            <option value="">Pilih</option>
                            <option value="tegak">Tegak</option>
                            <option value="miring">Miring</option>
                        </select>

                        <span>Kondisi</span>
                        <select name="tiang[kondisi]">
                            <option value="">Pilih</option>
                            <option value="aman">Aman</option>
                            <option value="parah">Parah</option>
                        </select>

                        <span>Kemiringan</span>
                        <select name="tiang[miring]">
                            <option value="">Pilih</option>
                            <option value="ringan">Ringan</option>
                            <option value="sedang">Sedang</option>
                            <option value="berat">Berat</option>
                        </select>

                        <input type="text" name="kondisi[tiang][catatan]" placeholder="Catatan...">
                    </div>


                    <!-- 5. KABEL DI CLAMP -->

                    <div class="row-item">
                        <label>5. Clamp</label>

                        <select name="clamp[status]">
                            <option value="">Pilih</option>
                            <option value="baik">Baik</option>
                            <option value="rusak">Rusak</option>
                        </select>

                        <span>Kondisi</span>
                        <select name="clamp[kondisi]">
                            <option value="">Pilih</option>
                            <option value="kendur">Kendur</option>
                            <option value="tergesek">Tergesek</option>
                            <option value="tertekan">Tertekan</option>
                        </select>

                        <span>-</span>
                        <span>-</span>

                        <input type="text" name="kondisi[clamp][catatan]" placeholder="Catatan...">
                    </div>

                    <!-- 6. LINGKUNGAN -->
                    <div class="row-item">
                        <label>6. Lingkungan</label>

                        <select name="lingkungan[status]">
                            <option value="">Pilih</option>
                            <option value="aman">Aman</option>
                            <option value="tidak_aman">Tidak Aman</option>
                        </select>

                        <span>Dampak</span>
                        <select name="lingkungan[dampak]">
                            <option value="">Pilih</option>
                            <option value="belum">Belum</option>
                            <option value="potensi">Potensi</option>
                            <option value="sudah">Sudah</option>
                        </select>

                        <span>-</span>
                        <span>-</span>

                        <input type="text" name="kondisi[lingkungan][catatan]" placeholder="Catatan...">
                    </div>

                    <!-- 7. VEGETASI -->
                    <div class="row-item">
                        <label>7. Vegetasi</label>

                        <select name="vegetasi[status]">
                            <option value="">Pilih</option>
                            <option value="aman">Aman</option>
                            <option value="tidak_aman">Tidak Aman</option>
                        </select>

                        <span>Jarak</span>
                        <select name="vegetasi[jarak]">
                            <option value="">Pilih</option>
                            <option value="dekat">Dekat</option>
                            <option value="sentuh">Sentuh</option>
                            <option value="tekan">Tekan</option>
                            <option value="tumbang">Tumbang</option>
                        </select>

                        <span>-</span>
                        <span>-</span>

                        <input type="text" name="kondisi[vegetasi][catatan]" placeholder="Catatan...">
                    </div>

                    <div class="row-item">
                        <label>8. Marker Post</label>
                        <select name="marker_post">
                            <option value="">Pilih</option>
                            <option value="baik">Baik</option>
                            <option value="rusak">Rusak</option>
                        </select>
                        <span>-</span><span>-</span><span>-</span><span>-</span>
                        <input type="text" name="kondisi[marker_post][catatan]" placeholder="Catatan...">
                    </div>

                    <div class="row-item">
                        <label>9. Hand Hole</label>
                        <select name="hand_hole">
                            <option value="">Pilih</option>
                            <option value="baik">Baik</option>
                            <option value="rusak">Rusak</option>
                        </select>
                        <span>-</span><span>-</span><span>-</span><span>-</span>
                        <input type="text" name="kondisi[hand_hole][catatan]" placeholder="Catatan...">
                    </div>

                    <div class="row-item">
                        <label>10. Aksesoris KU</label>
                        <select name="aksesoris_ku">
                            <option value="">Pilih</option>
                            <option value="baik">Baik</option>
                            <option value="rusak">Rusak</option>
                        </select>
                        <span>-</span><span>-</span><span>-</span><span>-</span>
                        <input type="text" name="kondisi[aksesoris_ku][catatan]" placeholder="Catatan...">
                    </div>

                    <div class="row-item">
                        <label>11. JC / ODP</label>
                        <select name="jc_odp">
                            <option value="">Pilih</option>
                            <option value="baik">Baik</option>
                            <option value="rusak">Rusak</option>
                        </select>
                        <span>-</span><span>-</span><span>-</span><span>-</span>
                        <input type="text" name="kondisi[jc_odp][catatan]" placeholder="Catatan...">
                    </div>




                </div>
            </div>
            <hr>
            <!-- ===================== UPLOAD GAMBAR ===================== -->
            <div class="section">
                <h3>C. Upload Bukti PM</h3>

                <div class="form-group">
                    <label>Upload Foto (Bisa lebih dari 1)</label>
                    <input type="file" id="images_input" name="images[]" multiple class="form-control" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                    <small style="display:block; margin-top:6px; color:#64748b;">
                        Maks 10 foto, format JPG/JPEG/PNG, maksimal 5 MB per foto, total disarankan <= 9 MB.
                    </small>

                    @if ($isEditDraft && $draft->images->count() > 0)
                        <small style="display:block; margin-top:6px; color:#2563eb;">
                            Jika tidak upload foto baru, foto lama tetap dipakai.
                        </small>

                        <div class="preview-grid">
                            @foreach ($draft->images as $img)
                                <a href="{{ asset('storage/' . $img->image_path) }}" target="_blank" class="preview-card">
                                    <img src="{{ asset('storage/' . $img->image_path) }}" alt="Foto bukti">
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- ===================== C. PENGESAHAN ===================== -->
            <div class="section">
                <h3>D. Pengesahan</h3>

                <div class="grid">

                    <!-- Prepared -->
                    <div>
                        <input type="text" name="prepared_by" value="{{ auth()->user()->username }}" readonly>

                        <p><b>Tanda tangan:</b></p>

                        <canvas id="canvas_prepared"></canvas>

                        <input type="hidden" name="signature_teknisi" id="prepared_canvas"
                            value="{{ $isEditDraft ? $draft->prepared_signature : '' }}">

                        @if ($isEditDraft && $draft->prepared_signature)
                            <div class="signature-preview">
                                <small style="display:block; color:#2563eb; margin-bottom:6px;">
                                    Tanda tangan sebelumnya (tetap dipakai kalau tidak digambar ulang)
                                </small>
                                <img src="{{ $draft->prepared_signature }}" alt="Tanda tangan teknisi">
                            </div>
                        @endif

                        <button type="button" onclick="clearPrepared()">Clear</button>
                    </div>

                    <!-- Approved -->
                    {{-- <div>
                    <label>Approved By</label>
                    <select name="approved_by" class="select2">
                        <option value="">-- Pilih Approver --</option>
                        @foreach ($approver as $a)
                            <option value="{{ $a->username }}">{{ $a->username }}</option>
                        @endforeach
                    </select>

                    <label>Tanda Tangan Approved (Upload)</label>
                    <input type="file" name="approved_signature" accept="image/*">

                    <p><b>atau tanda tangan manual:</b></p>
                    <canvas id="canvas_approved" width="320" height="160"></canvas>
                    <input type="hidden" name="approved_canvas" id="approved_canvas">
                    <button type="button" onclick="clearApproved()">Clear</button>
                </div> --}}

                </div>
            </div>

            <hr>

            <button type="submit" name="action" value="draft">
                Simpan Draft
            </button>

            <button type="submit" name="action" value="submit_ro">
                Kirim ke Kepala RO
            </button>
        </form>



</html>
</div>
</body>

<!-- ================= JS ================= -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

<script>
    const draftPayload = @json($draftPayload ?? null);

    function setCaraPatroliVisibility() {
        const select = document.getElementById('cara_patroli');
        const label = document.getElementById('label_cara_patroli_lainnya');
        const input = document.getElementById('cara_patroli_lainnya');

        if (!select || !label || !input) return;

        const isLainnya = select.value === 'lainnya';
        label.style.display = isLainnya ? 'block' : 'none';
        input.style.display = isLainnya ? 'block' : 'none';

        if (!isLainnya) {
            input.value = '';
        }
    }

    function setFieldValue(fieldName, value) {
        if (value === null || value === undefined) return;

        const selector = `[name="${fieldName.replace(/"/g, '\\"')}"]`;
        const field = document.querySelector(selector);
        if (!field) return;

        if (field.type === 'checkbox' || field.type === 'radio') {
            field.checked = String(field.value) === String(value);
            return;
        }

        field.value = value;
    }

    function applyPayloadRecursive(obj, prefix = '') {
        if (!obj || typeof obj !== 'object') return;

        Object.entries(obj).forEach(([key, value]) => {
            const fieldName = prefix ? `${prefix}[${key}]` : key;

            if (value !== null && typeof value === 'object' && !Array.isArray(value)) {
                applyPayloadRecursive(value, fieldName);
                return;
            }

            setFieldValue(fieldName, value);
        });
    }

    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({
                placeholder: 'Cari...',
                allowClear: true
            });
        }

        if (draftPayload) {
            applyPayloadRecursive(draftPayload);
        }

        const caraPatroli = document.getElementById('cara_patroli');
        if (caraPatroli) {
            caraPatroli.addEventListener('change', setCaraPatroliVisibility);
            setCaraPatroliVisibility();
        }

        if (draftPayload && draftPayload.nama_pelaksana) {
            $('#nama_pelaksana').val(draftPayload.nama_pelaksana).trigger('change');
        }
    });
</script>

<script>
    function setupCanvas(canvasId, inputId) {

        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        // ✅ FIX 1: set ukuran kecil (WAJIB)
        canvas.width = 300;
        canvas.height = 150;
        canvas.dataset.hasStroke = '0';

        const ctx = canvas.getContext("2d");

        let drawing = false;

        canvas.addEventListener("mousedown", start);
        canvas.addEventListener("mouseup", stop);
        canvas.addEventListener("mouseout", stop);
        canvas.addEventListener("mousemove", draw);

        function start(e) {
            drawing = true;
            draw(e);
        }

        function stop() {
            drawing = false;
            ctx.beginPath();
            save();
        }

        function draw(e) {
            if (!drawing) return;

            const rect = canvas.getBoundingClientRect();

            ctx.lineWidth = 2;
            ctx.lineCap = "round";
            ctx.strokeStyle = "#000";
            canvas.dataset.hasStroke = '1';

            ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
        }

        function save() {
            const input = document.getElementById(inputId);
            if (input) {
                const hasStroke = canvas.dataset.hasStroke === '1';
                if (!hasStroke && input.value) {
                    return;
                }

                input.value = canvas.toDataURL('image/png');
            }
        }

        // ✅ FIX 3: save terakhir sebelum submit (anti ga ke-trigger)
        document.querySelector("form").addEventListener("submit", function() {
            save();
        });
    }


    function clearPrepared() {
        const canvas = document.getElementById("canvas_prepared");
        if (!canvas) return;

        const ctx = canvas.getContext("2d");
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        canvas.dataset.hasStroke = '0';

        // ✅ reset hidden input
        document.getElementById("prepared_canvas").value = '';
    }


    // aktifkan canvas
    setupCanvas("canvas_prepared", "prepared_canvas");

    function formatMb(bytes) {
        return (bytes / (1024 * 1024)).toFixed(2);
    }

    function validateImagesBeforeSubmit(event) {
        const input = document.getElementById('images_input');
        if (!input || !input.files || input.files.length === 0) {
            return true;
        }

        const maxPerFile = 5 * 1024 * 1024; // 5MB
        const maxTotal = 9 * 1024 * 1024; // 9MB (aman dari limit server 10MB)
        let totalSize = 0;

        for (const file of input.files) {
            totalSize += file.size;

            if (file.size > maxPerFile) {
                event.preventDefault();
                alert(`File "${file.name}" berukuran ${formatMb(file.size)} MB. Maksimal 5 MB per file.`);
                return false;
            }
        }

        if (totalSize > maxTotal) {
            event.preventDefault();
            alert(`Total ukuran foto ${formatMb(totalSize)} MB. Maksimal total upload 9 MB per submit.`);
            return false;
        }

        return true;
    }

    document.querySelector("form").addEventListener("submit", validateImagesBeforeSubmit);



    function clearApproved() {
        const canvas = document.getElementById("canvas_approved");
        const ctx = canvas.getContext("2d");
        ctx.clearRect(0, 0, canvas.width, canvas.height);

    }


    function toggleSection(id) {
        const el = document.getElementById(id);

        if (el.style.display === "none" || el.style.display === "") {
            el.style.display = "block";
        } else {
            el.style.display = "none";
        }
    }
</script>
