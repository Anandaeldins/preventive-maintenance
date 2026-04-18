<?php

    namespace App\Http\Controllers;

    use App\Models\PmSchedule;
    use App\Models\InspeksiHeader;
    use App\Models\User;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Storage;
    use App\Models\Segment;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Validator;
use App\Models\FmeaOutput;

    class PmScheduleController extends Controller
    {

        /*
        |--------------------------------------------------------------------------
        | LIST SCHEDULE
        |--------------------------------------------------------------------------
        */
  public function index()
{
    $user = Auth::user();

    // ================= TEKNISI =================
    if ($user->role === 'admin') {
        $teknisis = User::where('role', 'teknisi')->get();
    } else {
        $teknisis = User::where('role', 'teknisi')
            ->where('regional_id', $user->regional_id)
            ->get();
    }

    // ================= SCHEDULE =================
    $schedules = PmSchedule::with(['segment', 'creator', 'teknisi1', 'teknisi2'])
        ->when($user->role !== 'admin', function ($query) use ($user) {
            $query->whereHas('segment', function ($q) use ($user) {
                $q->where('regional_id', $user->regional_id);
            });
        })
        ->orderBy('created_at', 'desc') // 🔥 terbaru dibuat
        ->get() // ❗ WAJIB
        ->groupBy(function ($item) {

            $segmentName = optional($item->segment)->nama_segment ?? 'Segment tidak ditemukan';

            return $segmentName . '|' .
                   Carbon::parse($item->planned_date)->format('Y-m') . '|' .
                   $item->teknisi_1 . '|' .
                   ($item->teknisi_2 ?? '0');
        });

    // ================= SEGMENT =================
    $segments = Segment::when($user->role !== 'admin', function ($query) use ($user) {
        $query->where('regional_id', $user->regional_id);
    })->orderBy('nama_segment')->get();

    return view('pm-schedules.index', compact(
        'schedules',
        'segments',
        'teknisis'
    ));
}

        /*
        |--------------------------------------------------------------------------
        | CREATE FORM
        |--------------------------------------------------------------------------
        */
   public function create()
{
    $user = Auth::user();

    // ✅ Segment hanya sesuai regional user login
    $segments = Segment::where('regional_id', $user->regional_id)
                ->orderBy('nama_segment')
                ->get();

    // ✅ (optional tapi disarankan) teknisi sesuai regional juga
    $teknisis = User::where('role', 'teknisi')
                ->where('regional_id', $user->regional_id)
                ->get();

    return view('pm-schedules.create',
        compact('segments','teknisis'));
}
        /*
        |--------------------------------------------------------------------------
        | STORE SCHEDULE
        |--------------------------------------------------------------------------
        */
        
public function store(Request $request)
{
    try {

        // ================= VALIDATION =================
        $validator = Validator::make($request->all(), [
            'segment_id' => 'required|exists:segments,id',
            'planned_date' => 'required|string',
            'teknisi_1' => 'required|integer',
        ]);

        $validator->after(function ($validator) use ($request) {

            $priority = strtoupper($request->priority ?? 'RENDAH');

            $dates = array_filter(
                array_map('trim', explode(',', $request->planned_date ?? ''))
            );

            $minRequired = match($priority) {
                'KRITIS' => 3,
                'SEDANG' => 2,
                default  => 1
            };

            if (count($dates) < $minRequired) {
                $validator->errors()->add(
                    'planned_date',
                    "Priority {$priority} membutuhkan minimal {$minRequired} jadwal."
                );
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // ================= PARSE DATE =================
        $dates = array_filter(
            array_map('trim', explode(',', $request->planned_date))
        );

        if (empty($dates)) {
            return back()->withErrors([
                'planned_date' => 'Pilih minimal satu tanggal.'
            ])->withInput();
        }

        $priority = strtoupper($request->priority ?? 'RENDAH');
        $submitForApproval = $request->submit_for_approval ?? 1;

        // ================= VALIDASI FORMAT & MASA LALU =================
        $today = now()->toDateString();

        foreach ($dates as $date) {

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return back()->withErrors([
                    'planned_date' => "Format tanggal {$date} tidak valid."
                ])->withInput();
            }

            if ($date < $today) {
                return back()->withErrors([
                    'planned_date' => "Tanggal {$date} tidak boleh di masa lalu."
                ])->withInput();
            }
        }

        // ================= TEKNISI VALIDATION =================
        $teknisi1 = $request->teknisi_1;
        $teknisi2 = $request->teknisi_2;

        if ($teknisi2 && $teknisi1 == $teknisi2) {
            return back()->withErrors([
                'teknisi_2' => 'Teknisi 1 dan Teknisi 2 tidak boleh sama.'
            ])->withInput();
        }

        $createdCount = 0;

        // ================= SIGNATURE =================
        $signaturePath = null;

        if ($request->signature_teknisi) {

            $image = $request->signature_teknisi;

            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);

            $imageData = base64_decode($image);

            $fileName = 'ttd_' . time() . '.png';

            Storage::disk('public')->put('signatures/' . $fileName, $imageData);

            $signaturePath = 'signatures/' . $fileName;
        }

        // ================= SAVE =================
        DB::transaction(function () use (
            $request,
            $dates,
            $priority,
            $teknisi1,
            $teknisi2,
            $submitForApproval,
            $signaturePath,
            &$createdCount
        ) {

            foreach ($dates as $date) {

                $status = $submitForApproval == 1 ? 'pending' : 'draft';

                PmSchedule::create([
                    'segment_id'        => $request->segment_id,
                    'planned_date'      => $date,
                    'priority'          => $priority,
                    'created_by'        => Auth::id(),
                    'status'            => $status,
                    'notes'             => $request->notes,
                    'teknisi_1'         => $teknisi1,
                    'teknisi_2'         => $teknisi2,
                    'signature_teknisi' => $signaturePath,
                ]);

                $createdCount++;
            }
        });

        return redirect()
            ->route('pm-schedules.index')
            ->with('success', "{$createdCount} jadwal PM berhasil dibuat.");

    } catch (\Throwable $e) {

        return back()->withErrors([
            'system_error' => 'SYSTEM ERROR: ' . $e->getMessage()
        ])->withInput();
    }
}

        /*
        |--------------------------------------------------------------------------
        | SUBMIT MANUAL APPROVAL
        |--------------------------------------------------------------------------
        */
       public function submitForApproval($id)
{
    $schedule = PmSchedule::findOrFail($id);

    if ($schedule->status !== 'draft') {
        return back()->withErrors([
            'error' => 'Jadwal sudah dalam proses approval.'
        ]);
    }

    $date = Carbon::parse($schedule->planned_date);

    // 🔥 update SEMUA tanggal dalam 1 batch
    PmSchedule::where('segment_id', $schedule->segment_id)
        ->whereMonth('planned_date', $date->month)
        ->whereYear('planned_date', $date->year)
        ->where('status', 'draft')
        ->update([
            'status' => 'pending'
        ]);

    return back()->with('success',
        'Semua jadwal PM berhasil dikirim untuk approval.'
    );
}
        
        /*
        |--------------------------------------------------------------------------
        | DELETE
        |--------------------------------------------------------------------------
        */
        public function destroy($id)
        {
            $schedule = PmSchedule::findOrFail($id);
            $schedule->delete();

            return redirect()
                ->route('pm-schedules.index')
                ->with('success','Jadwal PM berhasil dihapus.');
        }

        public function approvalDashboard()
        {
            return view('approval.dashboard');
        }

public function pendingSchedules()
{
    $schedules = PmSchedule::with(['segment','creator'])
        ->where('status','pending')
        ->orderBy('planned_date')
        ->get()
        ->groupBy(function ($item) {

            return $item->segment_id . '|' .
       Carbon::parse($item->planned_date)->format('Y-m') . '|' .
       $item->teknisi_1 . '|' .
       ($item->teknisi_2 ?? '0');

        });

    return view('approval.pending-schedules', compact('schedules'));
}
    

       public function approvalHistory()
{
    $approvedSchedules = PmSchedule::where('status', 'approved')
        ->with(['creator','segment','teknisi1','teknisi2'])
        ->latest()
        ->get();

    return view('approval.history', compact('approvedSchedules'));
}

       public function rejectedSchedules()
{
    $rejectedSchedules = PmSchedule::where('status', 'rejected')
        ->with(['creator','segment','teknisi1','teknisi2'])
        ->latest()
        ->get();

    return view('approval.rejected', compact('rejectedSchedules'));
}

        public function pendingReports()
        {
            $reports = InspeksiHeader::whereIn('status_workflow', ['pending_ro'])
                ->with('pmSchedule.creator')
                ->latest()
                ->get();

            return view('approval.pending-reports', compact('reports'));
        }

       public function approveByRo(Request $request, $id)
{
    $schedule = PmSchedule::findOrFail($id);

    if ($schedule->status !== 'pending') {
        return back()->withErrors([
            'error' => 'Schedule is not pending.'
        ]);
    }

    $signaturePath = null;

    if ($request->signature_ro) {

        $image = str_replace('data:image/png;base64,', '', $request->signature_ro);
        $image = str_replace(' ', '+', $image);

        $imageData = base64_decode($image);

        $fileName = 'ro_' . time() . '.png';

        Storage::disk('public')->put('signatures/'.$fileName, $imageData);

        $signaturePath = 'signatures/'.$fileName;
    }

    $schedule->update([
        'status' => 'approved',
        'signature_ro' => $signaturePath,
        'approved_by' => Auth::id()
    
    ]);

    return redirect()
        ->route('approval.pending.schedules')
        ->with('success', 'Approved by Kepala RO.');
}

       

        public function rejectSchedule($id)
        {
            $schedule = PmSchedule::findOrFail($id);
            $role = Auth::user()->role;

            if (!in_array($schedule->status, ['pending'])) {
                return back()->withErrors([
                    'error' => 'Schedule cannot be rejected.'
                ]);
            }

            $schedule->update([
                'status' => 'rejected'
            ]);

            if ($role === 'kepala_ro' || $role === 'admin') {
                return redirect()
                    ->route('approval.pending.schedules')
                    ->with('success', 'Schedule rejected by Kepala RO.');
            }

            

            return back()->with('success', 'Schedule rejected.');
        }
        
    public function update(Request $request, $id)
{
    try {

        $schedule = PmSchedule::findOrFail($id);

        $request->validate([
            'segment_id' => 'required|exists:segments,id',
            'planned_date' => 'required|string',
            'teknisi_1' => 'required|exists:users,id',
            'teknisi_2' => 'nullable|exists:users,id',
        ]);

        $dates = array_filter(
            array_map('trim', explode(',', $request->planned_date))
        );

        /*
        =============================
        HANDLE SIGNATURE
        =============================
        */
        $signaturePath = $schedule->signature_teknisi;

        if ($request->signature_teknisi) {

            $image = $request->signature_teknisi;

            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);

            $imageData = base64_decode($image);

            $fileName = 'ttd_' . time() . '.png';

            Storage::disk('public')->put('signatures/' . $fileName, $imageData);

            $signaturePath = 'signatures/' . $fileName;
        }

        DB::transaction(function () use ($schedule, $request, $dates, $signaturePath) {

            /*
            =============================
            DELETE 1 GROUP (1 BULAN)
            =============================
            */
            $group = PmSchedule::where('segment_id', $schedule->segment_id)
    ->whereMonth('planned_date', $schedule->planned_date->format('m'))
    ->whereYear('planned_date', $schedule->planned_date->format('Y'))
    ->where('teknisi_1', $schedule->teknisi_1)
    ->where(function ($q) use ($schedule) {
        if ($schedule->teknisi_2) {
            $q->where('teknisi_2', $schedule->teknisi_2);
        } else {
            $q->whereNull('teknisi_2');
        }
    })
    ->delete(); // 🔥 langsung hapus
            

            /*
            =============================
            INSERT ULANG (MULTI TANGGAL)
            =============================
            */
            foreach ($dates as $date) {

                PmSchedule::create([
                    'segment_id' => $request->segment_id,
                    'planned_date' => $date,
                    'priority' => $schedule->priority,
                    'created_by' => $schedule->created_by,
                    'status' => $schedule->status,
                    'teknisi_1' => $request->teknisi_1,
                    'teknisi_2' => $request->teknisi_2,
                    'notes' => $request->notes,
                    'signature_teknisi' => $signaturePath
                ]);
            }
        });

        return redirect()
            ->route('pm-schedules.index')
            ->with('success', 'Jadwal berhasil diperbarui.');

    } catch (\Throwable $e) {

        return back()->withErrors([
            'system_error' => $e->getMessage()
        ]);
    }
}
public function approveGroup(Request $request)
{
    // validasi input
    $request->validate([
        'group_id' => 'required'
    ]);

    // pecah segment dan bulan
    [$segment, $month] = explode('|', $request->group_id);

    $signaturePath = null;

    // proses tanda tangan RO jika ada
    if ($request->signature_ro) {

        $image = str_replace('data:image/png;base64,', '', $request->signature_ro);
        $image = str_replace(' ', '+', $image);

        $imageData = base64_decode($image);

        $fileName = 'ro_' . time() . '.png';

        Storage::disk('public')->put('signatures/' . $fileName, $imageData);

        $signaturePath = 'signatures/' . $fileName;
    }

    // update semua schedule dalam group (segment + bulan)
    PmSchedule::where('segment_id', $segment)
        ->whereMonth('planned_date', date('m', strtotime($month)))
        ->whereYear('planned_date', date('Y', strtotime($month)))
        ->update([
            'status' => 'approved',
            'signature_ro' => $signaturePath,
            'approved_by' => Auth::id()        ]);

    return back()->with('success', 'Schedule bulan ini disetujui');
}

public function rejectGroup(Request $request)
{
    $request->validate([
        'group_id' => 'required'
    ]);

    [$segment, $month] = explode('|', $request->group_id);

    PmSchedule::where('segment_id', $segment)
        ->whereMonth('planned_date', date('m', strtotime($month)))
        ->whereYear('planned_date', date('Y', strtotime($month)))
        ->update([
            'status' => 'rejected'
        ]);

    return back()->with('success', 'Schedule bulan ini ditolak');
}

public function getRiskSummary(Request $request)
{
    $segmentId = $request->segment_id;
    $bulan = (int) $request->bulan;
    $tahun = (int) $request->tahun;

    // =============================
    // AMBIL BULAN SEBELUMNYA
    // =============================
    if ($bulan == 1) {
        $bulan = 12;
        $tahun -= 1;
    } else {
        $bulan -= 1;
    }

    $fmea = FmeaOutput::where('segment_id', $segmentId)
        ->where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->first();

    return response()->json([
        'priority' => $fmea->priority ?? 'RENDAH'
    ]);
}
private function getMinimalSchedule($priority)
{
    return match ($priority) {
        'KRITIS' => 3,
        'SEDANG' => 2,
        default => 1,
    };
}
    }