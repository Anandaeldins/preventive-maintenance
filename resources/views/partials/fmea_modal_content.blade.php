@if ($results->count() > 0)

    <h5>Bulan: {{ $bulan ?? '-' }} / {{ $tahun ?? '-' }}</h5>
    <h5>Prioritas: {{ $priority }}</h5>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item</th>
                <th>Severity</th>
                <th>Occurrence</th>
                <th>Detection</th>
                <th>Avg RPN</th>
                <th>Risk Index</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($results as $r)
                <tr>
                    <td>{{ $r['item'] }}</td>
                    <td>{{ $r['severity'] }}</td>
                    <td>{{ $r['occurrence'] }}</td>
                    <td>{{ $r['detection'] }}</td>
                    <td>{{ $r['RPN'] }}</td>
                    <td>{{ round($r['index'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>Tidak ada data FMEA untuk bulan ini.</p>
@endif
