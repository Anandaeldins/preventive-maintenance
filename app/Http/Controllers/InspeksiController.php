<?php

namespace App\Http\Controllers;

use App\Models\InspeksiHeader;
use App\Models\PmSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\InspeksiDetail;
class InspeksiController extends Controller
{
    /**
     * Store new inspection - enforces schedule requirement and sets workflow status
     */
public function store(Request $request)
{
    $request->validate([
        'segment_inspeksi' => 'required|string|max:150',
        'tanggal_inspeksi' => 'required|date',
        'schedule_id' => 'required|exists:pm_schedules,id'
    ]);

    $schedule = PmSchedule::findOrFail($request->schedule_id);

    $status = $request->action === 'submit_ro'
        ? 'pending_ro'
        : 'draft';

    try {

        DB::transaction(function () use ($request, $schedule, $status) {

            // ================= INIT =================
            $results = [];

            $jalurFO = $request->jalur_fo ?? 'non_backbone';

            $rpnMax = [
                'kabel_putus' => 15,
                'kabel_expose' => 60,
                'penyangga' => 60,
                'tiang' => 30,
                'clamp' => 24,
                'lingkungan' => 75,
                'vegetasi' => 45,
            ];

            $adjustSeverity = function ($S) use ($jalurFO) {
                return ($jalurFO === 'backbone') ? min($S + 1, 5) : $S;
            };

           $hitung = function($item, $S, $O, $D) use (&$results) {

    $RPN = $S * $O * $D;

    // ✅ MAX GLOBAL FMEA
    $maxRPN = 125;

    $index = $RPN / $maxRPN;

    $results[] = [
        'item' => $item,
        'S' => $S,
        'O' => $O,
        'D' => $D,
        'RPN' => $RPN,
        'index' => round($index, 2)
    ];
};

            // ================= 7 ITEM =================

            if(strtolower($request->input('kabel_putus.status','')) === 'ya'){
                $S = match ($request->input('kabel_putus.dampak')) {
                    'down' => 5,
                    'sebagian' => 4,
                    'normal' => 3,
                    default => 2
                };
                $S = $adjustSeverity($S);
                $D = $request->input('kabel_putus.backup') === 'ada' ? 2 : 1;
                $O = $this->hitungOccurrence($request->segment_inspeksi,'kabel_putus','status','ya');
                $hitung('kabel_putus', $S, $O, $D);
            }

            if(strtolower($request->input('kabel_expose.status','')) === 'ada'){
                $S = match ($request->input('kabel_expose.pelindung')) {
                    'rusak' => 4,
                    'retak' => 3,
                    'utuh' => 2,
                    default => 1
                };
                $S = $adjustSeverity($S);
                $D = match ($request->input('kabel_expose.lingkungan')) {
                    'beban' => 3,
                    'tanah_air' => 2,
                    'aman' => 1,
                    default => 2
                };
                $O = $this->hitungOccurrence($request->segment_inspeksi,'kabel_expose','status','ada');
                $hitung('kabel_expose', $S, $O, $D);
            }

            if(strtolower($request->input('penyangga.status','')) === 'rusak'){
                $S = match ($request->input('penyangga.kondisi')) {
                    'lepas' => 4,
                    'retak' => 3,
                    'karat' => 2,
                    default => 1
                };
                $S = $adjustSeverity($S);
                $D = match ($request->input('penyangga.kabel')) {
                    'tertarik' => 3,
                    'menurun' => 2,
                    'aman' => 1,
                    default => 2
                };
                $O = $this->hitungOccurrence($request->segment_inspeksi,'penyangga','status','rusak');
                $hitung('penyangga', $S, $O, $D);
            }

            if(strtolower($request->input('tiang.posisi','')) === 'miring'){
                $S = match ($request->input('tiang.miring')) {
                    'berat' => 4,
                    'sedang' => 3,
                    'ringan' => 2,
                    default => 1
                };
                $S = $adjustSeverity($S);
                $D = $request->input('tiang.kondisi') === 'parah' ? 2 : 1;
                $O = $this->hitungOccurrence($request->segment_inspeksi,'tiang','posisi','miring');
                $hitung('tiang', $S, $O, $D);
            }

            if(strtolower($request->input('clamp.status','')) === 'rusak'){
                $S = match ($request->input('clamp.kondisi')) {
                    'tertekan' => 4,
                    'tergesek' => 3,
                    'kendur' => 2,
                    default => 1
                };
                $S = $adjustSeverity($S);
                $D = 2;
                $O = $this->hitungOccurrence($request->segment_inspeksi,'clamp','status','rusak');
                $hitung('clamp', $S, $O, $D);
            }

            if(strtolower($request->input('lingkungan.status','')) === 'tidak_aman'){
                $S = match ($request->input('lingkungan.dampak')) {
                    'sudah' => 4,
                    'potensi' => 3,
                    'belum' => 2,
                    default => 1
                };
                $S = $adjustSeverity($S);
                $D = 3;
                $O = $this->hitungOccurrence($request->segment_inspeksi,'lingkungan','status','tidak_aman');
                $hitung('lingkungan', $S, $O, $D);
            }

            if(strtolower($request->input('vegetasi.status','')) === 'tidak_aman'){
                $S = match ($request->input('vegetasi.jarak')) {
                    'tumbang' => 4,
                    'tekan' => 3,
                    'sentuh' => 2,
                    'dekat' => 1,
                    default => 1
                };
                $S = $adjustSeverity($S);
                $D = 3;
                $O = $this->hitungOccurrence($request->segment_inspeksi,'vegetasi','status','tidak_aman');
                $hitung('vegetasi', $S, $O, $D);
            }

            if(count($results) === 0){
                throw new \Exception('Tidak ada kondisi untuk FMEA');
            }

            // ================= PRIORITAS =================
            $maxIndex = collect($results)->max('index');

            if($maxIndex >= 0.8){
                $priority = 'KRITIS';
                $schedulePm = 'minimal pm 3x sebulan';
            }elseif($maxIndex >= 0.4){
                $priority = 'SEDANG';
                $schedulePm = 'minimal pm 2x sebulan';
            }else{
                $priority = 'RENDAH';
                $schedulePm = 'minimal pm 1x sebulan';
            }

            // ================= SIMPAN HEADER =================
            $inspeksi = InspeksiHeader::create([
                'segment_inspeksi' => $request->segment_inspeksi,
                'jalur_fo' => $request->jalur_fo,
                'nama_pelaksana' => $request->nama_pelaksana,
                'driver' => $request->driver,
                'cara_patroli' => $request->cara_patroli,
                'cara_patroli_lainnya' => $request->cara_patroli_lainnya,
                'tanggal_inspeksi' => $request->tanggal_inspeksi,
                'priority' => $priority,
                'schedule_pm' => $schedulePm,
                'prepared_by' => Auth::id(),
                'approved_by' => $request->approved_by,
                'prepared_signature' => $request->prepared_canvas,
                'approved_signature' => $request->approved_canvas,
                'schedule_id' => $schedule->id,
                'status_workflow' => $status
            ]);

            // ================= SIMPAN DETAIL =================
            foreach (['kabel_putus','kabel_expose','penyangga','tiang','clamp','lingkungan','vegetasi'] as $obj) {
                if ($request->has($obj)) {
                    $inspeksi->details()->create([
                        'objek' => $obj,
                        'status' => json_encode($request->$obj)
                    ]);
                }
            }

            // ================= SIMPAN FMEA =================
            foreach ($results as $r) {
                $inspeksi->fmeaDetails()->create([
                    'item' => $r['item'],
                    'severity' => $r['S'],
                    'occurrence' => $r['O'],
                    'detection' => $r['D'],
                    'rpn' => $r['RPN'],
                    'risk_index' => $r['index'],
                ]);
            }

        });

        return redirect('/tasks')->with('success', 'Laporan berhasil disimpan');

    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
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

    if ($jumlah >= 5) return 5;
    if ($jumlah >= 3) return 4;
    if ($jumlah >= 2) return 3;
    if ($jumlah == 1) return 2;

    return 1;
}


    /**
     * Submit inspection for RO approval (draft → pending_ro)
     */
    public function submitForApproval($id)
    {
        $inspeksi = InspeksiHeader::findOrFail($id);
        
        // Validate current status
        if ($inspeksi->status_workflow !== 'draft') {
            return back()->with('error', 'Inspeksi tidak dapat dikirim untuk approval dalam status saat ini.');
        }

        // Check if has schedule
        if (!$inspeksi->schedule_id) {
            return back()->with('error', 'Inspeksi harus terhubung dengan jadwal PM yang disetujui.');
        }

        // Update status to pending_ro
        $inspeksi->update(['status_workflow' => 'pending_ro']);

        return back()->with('success', 'Inspeksi berhasil dikirim untuk approval.');
    }

    public function riskSummary()
    {
        $segments = InspeksiHeader::with('fmeaDetails')
            ->select('segment_inspeksi')
            ->groupBy('segment_inspeksi')
            ->get()
            ->map(function ($segment) {
                $inspeksi = InspeksiHeader::where('segment_inspeksi', $segment->segment_inspeksi)
                    ->with('fmeaDetails')
                    ->latest()
                    ->first();

                return [
                    'segment' => $segment->segment_inspeksi,
                    'risk_summary' => $inspeksi ? $inspeksi->risk_summary : null,
                    'last_inspection' => $inspeksi ? $inspeksi->tanggal_inspeksi : null,
                ];
            });

        return view('admin.risk-summary', compact('segments'));
    }

    /**
     * Display inspection reports created by the logged-in technician
     */
    public function myReports()
    {
        // Get only reports created by the logged-in technician
        $reports = InspeksiHeader::where('prepared_by', Auth::id())
            ->with(['pmSchedule', 'approvals'])
            ->orderBy('tanggal_inspeksi', 'desc')
            ->get();

        return view('inspeksi.my-reports', compact('reports'));
    }

  public function approveByRo(Request $request,$id)
{
    $report = InspeksiHeader::findOrFail($id);

    $report->update([
        'approved_signature' => $request->signature_ro,
        'approved_by' => Auth::id(),// simpan user yang approve
        'status_workflow' => 'pending_pusat'
    ]);

    return back()->with('success','Report disetujui oleh Kepala RO.');
}

public function rejectByRo(Request $request,$id)
{

$report = InspeksiHeader::findOrFail($id);

$report->update([

'status_workflow'=>'rejected'

]);

return back()->with('success','Report ditolak oleh Kepala RO.');

}

public function approveByPusat($id)
{
    $report = InspeksiHeader::findOrFail($id);

    if ($report->status_workflow !== 'pending_pusat') {
        return back()->with('error','Report tidak dalam status pending pusat.');
    }

    $report->update([
        'status_workflow' => 'approved'
    ]);

    return back()->with('success','Report disetujui oleh pusat.');
}


public function rejectByPusat($id)
{
    $report = InspeksiHeader::findOrFail($id);

    $report->update([
        'status_workflow' => 'rejected'
    ]);

    return back()->with('success','Report ditolak oleh pusat.');
}

public function pendingRO()
{
    $reports = InspeksiHeader::where('status_workflow','pending_ro')
        ->with(['pmSchedule.segment'])
        ->latest()
        ->get();

    return view('approval.ro-reports', compact('reports'));
}

public function pendingPusat()
{
    $reports = InspeksiHeader::where('status_workflow','pending_pusat')
        ->with(['pmSchedule.segment'])
        ->latest()
        ->get();

    return view('approval.pusat-reports', compact('reports'));

}

public function modal($id)
{
    $report = InspeksiHeader::with([
        'pmSchedule.segment',
        'kondisiUmum',
        'fmeaDetails',
        'details'
    ])->findOrFail($id);

    return view('inspeksi.modal-report', compact('report'));
}
}