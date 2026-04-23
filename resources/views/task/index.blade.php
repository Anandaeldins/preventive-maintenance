@extends('layouts.bar')

@section('content')
    <div class="container mt-4">

        <h2 class="mb-3 fw-bold">Task Maintenance</h2>

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

        <a href="{{ route('maintenance.info') }}" class="btn btn-sm mb-4"
            style="
                background: var(--gradient);
                color:white;
                border:none;
                border-radius:8px;
            ">
            Informasi Maintenance
        </a>

        <div class="row">

            @foreach ($segments as $segment)
                @if ($segment->schedules->count() > 0)
                    <div class="col-md-6 mb-4">

                        <div class="card border-0 shadow-sm"
                            style="border-radius:14px; overflow:hidden; background:var(--card);">

                            {{-- HEADER --}}
                            <div onclick="toggleSegment({{ $segment->id }})"
                                style="
                                    cursor:pointer;
                                    background: var(--gradient);
                                    color:white;
                                    padding:14px 18px;
                                    font-weight:600;
                                    font-size:15px;
                                ">
                                {{ $segment->nama_segment }}
                            </div>

                            {{-- BODY --}}
                            <div class="card-body" id="segment-{{ $segment->id }}"
                                style="display:none; background: var(--bg);">

                                @foreach ($segment->schedules as $schedule)
                                    @if ($schedule->status === 'rejected' && !$schedule->inspeksiHeader)
                                        <button class="btn w-100 mb-2" disabled
                                            style="
                                                background:#fee2e2;
                                                color:#991b1b;
                                                border:none;
                                                border-radius:10px;
                                            ">
                                            {{ \Carbon\Carbon::parse($schedule->planned_date)->format('d F Y') }}<br>
                                            <small>Ditolak otomatis (terlewat)</small>
                                        </button>
                                    @elseif ($schedule->inspeksiHeader)
                                        <button class="btn w-100 mb-2" disabled
                                            style="
                                                background:#e2e8f0;
                                                color:#64748b;
                                                border:none;
                                                border-radius:10px;
                                            ">
                                            {{ \Carbon\Carbon::parse($schedule->planned_date)->format('d F Y') }}
                                        </button>
                                    @else
                                        <a href="{{ route('tasks.show', $schedule->id) }}" class="btn w-100 mb-2"
                                            style="
                                                background: var(--gradient);
                                                color:white;
                                                border:none;
                                                border-radius:10px;
                                                transition:0.2s;
                                            "
                                            onmouseover="this.style.transform='scale(1.02)'"
                                            onmouseout="this.style.transform='scale(1)'">
                                            {{ \Carbon\Carbon::parse($schedule->planned_date)->format('d F Y') }}
                                        </a>
                                    @endif
                                @endforeach

                            </div>

                        </div>

                    </div>
                @endif
            @endforeach

        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function toggleSegment(id) {
            let el = document.getElementById('segment-' + id);

            if (el.style.display === 'none') {
                el.style.display = 'block';
            } else {
                el.style.display = 'none';
            }
        }
    </script>
@endpush
