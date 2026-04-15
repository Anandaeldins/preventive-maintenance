@extends('layouts.bar')

@section('content')

    @push('style')
        <style>
            /* ============================= */
            /* WRAPPER GLASS EFFECT */
            /* ============================= */
            .calendar-wrapper {
                padding: 20px;
                border-radius: 20px;

                /* GLASS BACKGROUND */
                background: rgba(255, 255, 255, 0.25);
                backdrop-filter: blur(14px);
                -webkit-backdrop-filter: blur(14px);

                border: 1px solid rgba(255, 255, 255, 0.3);
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            }



            /* ============================= */
            /* DAYS HEADER */
            /* ============================= */
            .calendar-days {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 10px;
                margin-bottom: 10px;
                font-weight: 600;
                text-align: center;
            }

            .calendar-days div {
                background: rgba(241, 245, 249, .8);
                backdrop-filter: blur(6px);
                border-radius: 12px;
                padding: 8px 0;
                font-size: 13px;
                letter-spacing: .5px;
                box-shadow: 0 2px 6px rgba(0, 0, 0, .05);
            }



            /* ============================= */
            /* GRID TANGGAL */
            /* ============================= */
            .calendar-grid {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 10px;
                width: 100%;
            }



            /* ============================= */
            /* BUTTON TANGGAL */
            /* ============================= */
            .calendar-grid button {

                border: 1.5px solid #000;
                border-radius: 14px;

                /* FLEXIBLE SIZE */
                width: 100%;
                height: 50px;

                padding: 0;
                font-size: 14px;
                font-weight: 500;

                /* GLASS BUTTON */
                background: rgba(238, 238, 238, 0.75);
                backdrop-filter: blur(6px);

                cursor: pointer;

                display: flex;
                align-items: center;
                justify-content: center;

                transition: all .25s ease;
                box-shadow: 0 4px 10px rgba(0, 0, 0, .08);
            }



            /* ============================= */
            /* HOVER EFFECT */
            /* ============================= */
            .calendar-grid button:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 18px rgba(0, 0, 0, .15);
            }



            /* ============================= */
            /* CLICK EFFECT */
            /* ============================= */
            .calendar-grid button:active {
                transform: scale(.95);
            }



            /* ============================= */
            /* ACTIVE DATE */
            /* ============================= */
            .calendar-grid button.active {
                background: #16a34a !important;
                color: #fff !important;
                border-color: #16a34a;
                box-shadow: 0 8px 18px rgba(22, 163, 74, .45);
            }



            /* ============================= */
            /* ANIMASI BODY CALENDAR */
            /* ============================= */
            .calendar-body {
                position: relative;
                overflow: hidden;
            }



            /* SLIDE MASUK */
            .calendar-slide-in {
                animation: slideIn .35s ease;
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateX(40px);
                }

                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }



            /* SLIDE KELUAR */
            .calendar-slide-out {
                animation: slideOut .35s ease;
            }

            @keyframes slideOut {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }

                to {
                    opacity: 0;
                    transform: translateX(-40px);
                }
            }

            .dot {
                width: 14px;
                height: 14px;
                border-radius: 50%;
                display: inline-block;
            }

            .red {
                background: #ef4444;
            }

            .green {
                background: #16a34a;
            }

            .gray {
                background: #d1d5db;
            }
        </style>
    @endpush


    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-11">

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Buat Jadwal PM Baru</h5>
                    </div>

                    <div class="card-body">
                        {{-- ================= ERROR DISPLAY ================= --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <strong>Terjadi Kesalahan:</strong>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- ================= SUCCESS DISPLAY ================= --}}
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form action="{{ route('pm-schedules.store') }}" method="POST">
                            @csrf

                            <div class="row g-4">

                                {{-- ================= LEFT ================= --}}
                                <div class="col-md-4">

                                    <label>Segment</label>

                                    <select name="segment_id" id="segment_id" class="form-control" required>
                                        <option value="">-- Pilih Segment --</option>

                                        @foreach ($segments as $segment)
                                            <option value="{{ $segment->id }}">
                                                {{ $segment->kode_segment }} - {{ $segment->nama_segment }}
                                            </option>
                                        @endforeach

                                    </select>


                                    {{-- PRIORITY --}}
                                    <div class="d-flex gap-2 mt-3">
                                        <input type="text" class="form-control w-50" value="priority" readonly>

                                        <input type="text" name="priority" id="priority" class="form-control" readonly>
                                    </div>

                                    <small id="priorityNote" class="text-muted">
                                        Minimal 1 jadwal
                                    </small>
                                    <br>

                                    {{-- SIGNATURE --}}
                                    <label class="mt-3">Tanda Tangan Teknisi</label>

                                    <canvas id="signatureCanvas" width="300" height="150"
                                        style="border:2px solid #000;border-radius:10px;">
                                    </canvas>

                                    <input type="hidden" name="signature_teknisi" id="signature_teknisi">

                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="clearSignature()">
                                            Hapus
                                        </button>

                                        <button type="button" class="btn btn-sm btn-primary" onclick="previewSignature()">
                                            Preview
                                        </button>
                                    </div>

                                    <img id="signaturePreview"
                                        style="display:none;margin-top:10px;
                                        border:1px solid #ccc;
                                        max-width:100%;">

                                </div>


                                {{-- ================= CENTER ================= --}}
                                <div class="col-md-5">

                                    <div class="d-flex gap-2 mb-3">

                                        <input type="text" id="dateDisplay" class="form-control" placeholder="Pilih "
                                            readonly>

                                        <select id="monthSelect" class="form-select">
                                            @foreach (range(1, 12) as $m)
                                                <option value="{{ $m }}">
                                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select id="yearSelect" class="form-select">
                                            @foreach (range(date('Y'), date('Y') + 2) as $y)
                                                <option value="{{ $y }}">{{ $y }}</option>
                                            @endforeach
                                        </select>

                                    </div>

                                    <!-- HEADER HARI -->


                                    <!-- GRID TANGGAL -->
                                    <div class="calendar-grid" id="calendarGrid"></div>

                                    <div class="mt-3">
                                        <span class="dot red"></span> weekend<br>
                                        <span class="dot green"></span> dipilih<br>
                                    </div>

                                    <input type="hidden" name="planned_date" id="planned_dates">

                                </div>


                                {{-- ================= RIGHT ================= --}}
                                <div class="col-md-3">

                                    <label>Dibuat Oleh</label>

                                    <input type="text" class="form-control" value="{{ auth()->user()->username }}"
                                        readonly>

                                    <input type="hidden" name="teknisi_1" value="{{ auth()->user()->id }}">

                                    {{-- <label class="mt-3">Teknisi </label>
                                    <select name="teknisi_2" class="form-select">
                                        <option value="">-- pilih --</option>
                                        @foreach ($teknisis as $t)
                                            <option value="{{ $t->id }}">{{ $t->username }}</option>
                                        @endforeach
                                    </select> --}}

                                    {{-- NOTES FIELD --}}
                                    <label class="mt-3">Catatan</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan..."
                                        maxlength="500"></textarea>
                                    <small class="text-muted">Maksimum 500 karakter</small>

                                </div>

                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" name="submit_for_approval" value="0"
                                    class="btn btn-secondary px-4 me-2">
                                    <i class="bi bi-save"></i> Simpan Draft
                                </button>
                                <button type="submit" name="submit_for_approval" value="1"
                                    class="btn btn-primary px-4">
                                    <i class="bi bi-send"></i> Kirim untuk Approval
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection



@push('scripts')
    <script>
        // ================= PRIORITY NOTE =================
        function updatePriorityNote() {

            let val =
                document.getElementById('priority')
                .value.toUpperCase().trim();

            let note =
                document.getElementById('priorityNote');

            if (val === 'KRITIS') {
                note.innerText = 'Minimal 3 jadwal';
            } else if (val === 'SEDANG') {
                note.innerText = 'Minimal 2 jadwal';
            } else {
                note.innerText = 'Minimal 1 jadwal';
            }
        }


        // ================= PRIORITY AJAX =================
        $('#segment_id').change(function() {
            $.get('{{ route('pm-schedules.risk-summary') }}', {
                    segment_id: $(this).val()
                },
                res => {
                    $('#priority').val(res.priority ?? '');
                    updatePriorityNote();
                });

        });


        // ================= CANVAS SIGNATURE =================
        const canvas =
            document.getElementById('signatureCanvas');

        const ctx =
            canvas.getContext('2d');

        let drawing = false;

        function startDraw(e) {
            drawing = true;
            draw(e);
        }

        function endDraw() {
            drawing = false;
            ctx.beginPath();
            saveSignature();
        }

        function draw(e) {

            if (!drawing) return;

            let rect =
                canvas.getBoundingClientRect();

            let x =
                (e.clientX || e.touches[0].clientX) -
                rect.left;

            let y =
                (e.clientY || e.touches[0].clientY) -
                rect.top;

            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#000';

            ctx.lineTo(x, y);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(x, y);
        }

        // EVENTS
        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mouseup', endDraw);
        canvas.addEventListener('mousemove', draw);

        canvas.addEventListener('touchstart', startDraw);
        canvas.addEventListener('touchend', endDraw);
        canvas.addEventListener('touchmove', draw);

        function clearSignature() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('signature_teknisi').value = '';
            document.getElementById('signaturePreview').style.display = 'none';
        }

        function saveSignature() {
            document.getElementById('signature_teknisi').value =
                canvas.toDataURL('image/png');
        }

        function previewSignature() {
            let img =
                document.getElementById('signaturePreview');

            img.src = canvas.toDataURL('image/png');
            img.style.display = 'block';
        }


        // ================= CALENDAR =================
        document.addEventListener("DOMContentLoaded", () => {

            let selected = [];

            // ✅ FIX — deklarasi elemen
            let planned_dates =
                document.getElementById('planned_dates');

            let calendarGrid =
                document.getElementById('calendarGrid');

            let monthSelect =
                document.getElementById('monthSelect');

            let yearSelect =
                document.getElementById('yearSelect');


            // 🔹 HEADER HARI
            const daysHeader = document.createElement('div');
            daysHeader.className = "calendar-days";

            ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"]
            .forEach(d => {
                let el = document.createElement('div');
                el.innerText = d;
                daysHeader.appendChild(el);
            });

            calendarGrid.before(daysHeader);


            // 🔹 RENDER FUNCTION
            function render() {

                let m = parseInt(monthSelect.value);
                let y = parseInt(yearSelect.value);

                let today = new Date();
                today.setHours(0, 0, 0, 0);

                let days = new Date(y, m, 0).getDate();
                let firstDay = new Date(y, m - 1, 1).getDay();
                if (firstDay === 0) firstDay = 7;

                calendarGrid.innerHTML = '';

                for (let i = 1; i < firstDay; i++) {
                    let empty = document.createElement('div');
                    calendarGrid.appendChild(empty);
                }

                for (let i = 1; i <= days; i++) {

                    let btn = document.createElement('button');
                    btn.type = 'button';
                    btn.innerText = i;

                    let dateObj = new Date(y, m - 1, i);
                    dateObj.setHours(0, 0, 0, 0);

                    let date =
                        y + '-' +
                        String(m).padStart(2, '0') + '-' +
                        String(i).padStart(2, '0');

                    // ❌ DISABLE JIKA SEBELUM HARI INI
                    if (dateObj < today) {
                        btn.disabled = true;
                        btn.style.background = '#d1d5db';
                        btn.style.cursor = 'not-allowed';
                        calendarGrid.appendChild(btn);
                        continue;
                    }

                    // Weekend merah
                    let d = dateObj.getDay();
                    if (d === 0 || d === 6) {
                        btn.style.background = '#ef4444';
                        btn.style.color = '#fff';
                    }

                    btn.onclick = () => {

                        if (selected.includes(date)) {
                            selected = selected.filter(x => x !== date);
                            btn.classList.remove('active');
                        } else {
                            selected.push(date);
                            btn.classList.add('active');
                        }

                        planned_dates.value = selected.join(',');
                        document.getElementById('dateDisplay').value = selected.join(', ');
                    };

                    calendarGrid.appendChild(btn);
                }
            }

            // change month/year
            monthSelect.onchange = render;
            yearSelect.onchange = render;

            render();

        });


        // ================= VALIDATE BEFORE SUBMIT =================
        document.querySelector('form')
            .addEventListener('submit', function(e) {

                let dates = document.getElementById('planned_dates').value;

                if (!dates) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Pilih minimal 1 tanggal!'
                    });
                }
            });
    </script>
@endpush
