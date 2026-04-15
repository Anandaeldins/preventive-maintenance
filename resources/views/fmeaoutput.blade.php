@extends('layouts.bar')

@section('title', 'Hasil Perhitungan FMEA')

@section('content')
    <div class="fmea-wrapper">

        <div class="fmea-card">

            <div class="fmea-header">
                <h4>Daftar Segment FMEA</h4>
                <p>Analisis prioritas berdasarkan Risk Priority Number</p>
            </div>

            <form method="GET" action="{{ route('fmea.page') }}">
                <div class="row g-3 mb-4">

                    <!-- BULAN -->
                    <div class="col-md-4">
                        <label class="form-label">Bulan</label>
                        <select name="bulan" class="form-select custom-input">
                            <option value="">-- Pilih Bulan --</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ request('bulan') == $i ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <!-- TAHUN -->
                    <div class="col-md-4">
                        <label class="form-label">Tahun</label>
                        <select name="tahun" class="form-select custom-input">
                            @for ($y = 2024; $y <= 2030; $y++)
                                <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <!-- BUTTON -->
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-gradient w-100">
                            <i class="bi bi-filter"></i> Tampilkan
                        </button>
                    </div>

                </div>
            </form>

            <div class="table-modern">
                <table>
                    <thead>
                        <tr>
                            <th>Segment</th>
                            <th>Prioritas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($segments as $segment)
                            @php $priority = $dataPriority[$segment] ?? null; @endphp

                            <tr class="row-hover">

                                <td>{{ $segment }}</td>

                                <td>
                                    @if ($priority == 'KRITIS')
                                        <span class="badge-modern danger">KRITIS</span>
                                    @elseif ($priority == 'SEDANG')
                                        <span class="badge-modern warning">SEDANG</span>
                                    @elseif ($priority == 'RENDAH')
                                        <span class="badge-modern success">RENDAH</span>
                                    @else
                                        <span class="badge-modern secondary">Belum dihitung</span>
                                    @endif
                                </td>

                                <td>
                                    <button class="btn btn-sm btn-outline-modern viewFmea"
                                        data-segment="{{ $segment }}" data-bs-toggle="modal"
                                        data-bs-target="#fmeaModal">
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
    <!-- MODAL FMEA -->
    <div class="modal fade" id="fmeaModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Hasil Perhitungan FMEA</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="fmeaResult">

                        <div id="fmeaResult">
                            <p class="text-muted">Silakan klik "View" untuk melihat hasil FMEA</p>
                        </div>

                    </div>


                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>

            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            document.querySelectorAll('.viewFmea').forEach(btn => {
                btn.addEventListener('click', function() {

                    let segment = this.dataset.segment;

                    let bulan = document.querySelector('[name=bulan]').value;
                    let tahun = document.querySelector('[name=tahun]').value;
                    if (!bulan || !tahun) {
                        document.getElementById("fmeaResult").innerHTML =
                            "<p>Silakan pilih bulan & tahun terlebih dahulu</p>";
                        return;
                    }
                    console.log("FETCH:", segment, bulan, tahun);

                    fetch(`/fmeaoutput/data?segment=${segment}&bulan=${bulan}&tahun=${tahun}`).then(
                            res => res.json())
                        .then(data => {
                            document.getElementById("fmeaResult").innerHTML = data.html;
                        })
                        .catch(err => {
                            console.error(err);
                            document.getElementById("fmeaResult").innerHTML =
                                "Error loading data";
                        });

                });
            });

        });
    </script>

@endsection
@push('style')
    <style>
        .fmea-wrapper {
            padding-top: 20px;
        }

        /* CARD */
        .fmea-card {
            background: var(--card);
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 20px 50px rgba(37, 99, 235, 0.15);
            transition: 0.3s;
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

        /* INPUT */
        .custom-input {
            border-radius: 10px;
            border: 1px solid #dbeafe;
        }

        /* BUTTON */
        .btn-gradient {
            background: linear-gradient(135deg, #60a5fa, #2563eb);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
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

        /* ROW HOVER */
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

        .badge-modern.danger {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-modern.warning {
            background: #fef9c3;
            color: #ca8a04;
        }

        .badge-modern.success {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge-modern.secondary {
            background: #e2e8f0;
            color: #475569;
        }

        /* BUTTON VIEW */
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
