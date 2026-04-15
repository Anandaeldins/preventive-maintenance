<?php

namespace App\Http\Controllers\Teknisi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InspeksiHeader;
use App\Models\InspeksiKondisiUmum;
use App\Models\InspeksiDetail;
use App\Models\PmSchedule;

class FmeaController extends Controller
{
    /**
     * Get available approved schedules for today
     */
    private function getAvailableSchedules()
    {
return PmSchedule::where('status', 'approved')->get();
            
            
    }

 public function index(Request $request)
{
    $segments = InspeksiHeader::select('segment_inspeksi')
                    ->distinct()
                    ->pluck('segment_inspeksi');

    $dataPriority = [];

    foreach ($segments as $segment) {

        if (!$request->bulan || !$request->tahun) {
            $dataPriority[$segment] = null;
            continue;
        }

        $details = \App\Models\InspeksiFmeaDetail::whereHas('header', function ($q) use ($segment, $request) {
            $q->where('segment_inspeksi', $segment)
              ->whereMonth('tanggal_inspeksi', $request->bulan)
              ->whereYear('tanggal_inspeksi', $request->tahun);
        })->get();

        if ($details->count() == 0) {
            $dataPriority[$segment] = null;
            continue;
        }

        $maxIndex = $details->max('risk_index');

        $maxRpn = $details->max('rpn');

if ($maxRpn >= 20) {
    $priority = 'KRITIS';
} elseif ($maxRpn >= 10) {
    $priority = 'SEDANG';
} else {
    $priority = 'RENDAH';
}

        $dataPriority[$segment] = $priority;
    }

    return view('fmeaoutput', compact('segments', 'dataPriority'));
}
    public function hasil(Request $request, $id = null)
    {
        $segments = InspeksiHeader::select('segment_inspeksi')->distinct()->pluck('segment_inspeksi');
        $selectedSegment = $request->get('segment', $segments->first());

        if ($id) {
            $inspeksi = InspeksiHeader::findOrFail($id);
        } else {
            $inspeksi = InspeksiHeader::where('segment_inspeksi', $selectedSegment)->latest()->first();
        }

        if (!$inspeksi) {
            return view('hasilfmea', compact('segments', 'selectedSegment'))->with('error', 'Data inspeksi tidak ditemukan untuk segment ini');
        }

        $kondisiUmum = InspeksiKondisiUmum::where('inspeksi_id', $inspeksi->id)->first();

        $data = [
            'segment_inspeksi' => $inspeksi->segment_inspeksi,
            'jalur_fo' => $inspeksi->jalur_fo,
            'nama_pelaksana' => $inspeksi->nama_pelaksana,
            'driver' => $inspeksi->driver,
            'cara_patroli' => $inspeksi->cara_patroli,
            'tanggal_inspeksi' => $inspeksi->tanggal_inspeksi,
            'prepared_by' => $inspeksi->prepared_by,
            'approved_by' => $inspeksi->approved_by,
            'kabel_putus' => [
                'status' => 'ya', // Assuming from logic, but need to adjust based on saved data
                'backup' => 'ada',
                'dampak' => 'down',
            ],
            'kabel_expose' => [
                'status' => 'ada',
                'pelindung' => 'rusak',
                'lingkungan' => 'aman',
            ],
            'penyangga' => [
                'status' => 'rusak',
                'kondisi' => 'lepas',
                'kabel' => 'aman',
            ],
            'tiang' => [
                'posisi' => 'miring',
                'kondisi' => 'parah',
                'miring' => 'berat',
            ],
            'clamp' => [
                'status' => 'rusak',
                'kondisi' => 'tertekan',
            ],
            'lingkungan' => [
                'status' => 'tidak_aman',
                'dampak' => 'sudah',
            ],
            'vegetasi' => [
                'status' => 'tidak_aman',
                'jarak' => 'tumbang',
            ],
            'marker_post' => $kondisiUmum->marker_post ?? 'baik',
            'hand_hole' => $kondisiUmum->hand_hole ?? 'baik',
            'aksesoris_ku' => $kondisiUmum->aksesoris_ku ?? 'baik',
            'jc_odp' => $kondisiUmum->jc_odp ?? 'baik',
            'kondisi' => [
                'kabel_putus' => ['catatan' => ''],
                'kabel_expose' => ['catatan' => ''],
                'penyangga' => ['catatan' => ''],
                'tiang' => ['catatan' => ''],
                'clamp' => ['catatan' => ''],
                'lingkungan' => ['catatan' => ''],
                'vegetasi' => ['catatan' => ''],
                'marker_post' => ['catatan' => ''],
                'hand_hole' => ['catatan' => ''],
                'aksesoris_ku' => ['catatan' => ''],
                'jc_odp' => ['catatan' => ''],
            ],
        ];

        return view('hasilfmea', compact('data', 'segments', 'selectedSegment'));
    }


 public function output(Request $request)
{
    $segment = strtolower(trim($request->segment));
    $bulan = $request->bulan;
    $tahun = $request->tahun;

    $query = InspeksiHeader::with('fmeaDetails')
        ->whereRaw('LOWER(segment_inspeksi) = ?', [$segment]);

    if ($bulan && $tahun) {
        $query->whereMonth('tanggal_inspeksi', $bulan)
              ->whereYear('tanggal_inspeksi', $tahun);
    }

    $inspeksis = $query->get();

    if ($inspeksis->isEmpty()) {
        return response()->json([
            'html' => '<p>Tidak ada data FMEA untuk bulan ini.</p>'
        ]);
    }

    // ================= REKAP =================
    $rekap = [];

    foreach ($inspeksis as $inspeksi) {
        foreach ($inspeksi->fmeaDetails as $detail) {

            $item = $detail->item;

            if (!isset($rekap[$item])) {
                $rekap[$item] = [
                    'total_rpn' => 0,
                    'total_severity' => 0,
                    'total_occurrence' => 0,
                    'total_detection' => 0,
                    'count' => 0
                ];
            }

            $rekap[$item]['total_rpn'] += $detail->rpn;

            $rekap[$item]['total_severity'] += $detail->severity;
            $rekap[$item]['total_occurrence'] += $detail->occurrence;
            $rekap[$item]['total_detection'] += $detail->detection;

            $rekap[$item]['count']++;
        }
    }

    // ================= HITUNG RATA-RATA =================
    foreach ($rekap as $item => $data) {
        $rekap[$item]['avg_rpn'] = round($data['total_rpn'] / $data['count'], 2);

        $rekap[$item]['avg_severity'] = round($data['total_severity'] / $data['count'], 1);
        $rekap[$item]['avg_occurrence'] = round($data['total_occurrence'] / $data['count'], 1);
        $rekap[$item]['avg_detection'] = round($data['total_detection'] / $data['count'], 1);
    }

    // ================= NORMALISASI RISK INDEX =================
    $maxRpn = collect($rekap)->max('avg_rpn') ?? 1;

    foreach ($rekap as $item => $data) {
        $rekap[$item]['avg_index'] = round($data['avg_rpn'] / $maxRpn, 2);
    }

    // ================= PRIORITY =================
    if ($maxRpn >= 20) {
        $priority = 'KRITIS';
    } elseif ($maxRpn >= 10) {
        $priority = 'SEDANG';
    } else {
        $priority = 'RENDAH';
    }

    // ================= FORMAT KE VIEW =================
    $results = collect($rekap)->map(function ($data, $item) {
        return [
            'item' => $item,
            'severity' => $data['avg_severity'],
            'occurrence' => $data['avg_occurrence'],
            'detection' => $data['avg_detection'],
            'RPN' => $data['avg_rpn'],
            'index' => $data['avg_index']
        ];
    });

    // ================= RENDER =================
    $html = view('partials.fmea_modal_content', compact(
        'results',
        'priority',
        'maxRpn',
        'bulan',
        'tahun'
    ))->render();

    return response()->json(['html' => $html]);
}
private function hitungOccurrence($segment, $objek, $field, $value)
{
$inspeksiIds = InspeksiHeader::whereRaw('LOWER(segment_inspeksi) = ?', [strtolower($segment)])
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->pluck('id');

    $details = InspeksiDetail::whereIn('inspeksi_id', $inspeksiIds)
        ->where('objek', $objek)
        ->get();

    $jumlah = 0;

    foreach ($details as $d) {
        $status = json_decode($d->status, true);

        if (isset($status[$field]) && $status[$field] == $value) {
            $jumlah++;
        }
    }

    // skala 1–5
    if ($jumlah >= 5) return 5;
    if ($jumlah >= 3) return 4;
    if ($jumlah >= 2) return 3;
    if ($jumlah == 1) return 2;

    return 1;

    }


}