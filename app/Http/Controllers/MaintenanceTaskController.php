<?php

namespace App\Http\Controllers;
 use Illuminate\Support\Facades\Auth;
 use App\Models\Segment;
 use App\Models\PmSchedule;
 use App\Models\User;
 use Illuminate\Http\Request;
class MaintenanceTaskController extends Controller
{



public function index()
{
    $userId = Auth::id();

  $segments = Segment::with(['schedules' => function ($query) use ($userId) {

    $query->where('status', 'approved')
          ->where(function ($q) use ($userId) {
              $q->where('teknisi_1', $userId)
                ->orWhere('teknisi_2', $userId);
          })
          ->with('inspeksiHeader')   // penting
          ->orderBy('planned_date');

}])->get();

    return view('task.index', compact('segments'));
}




public function show($schedule)
{
    $schedule = PmSchedule::with('segment')->findOrFail($schedule);

    $user = Auth::user();

    // ✅ teknisi sesuai regional
    $teknisi = User::where('role','teknisi')
        ->where('regional_id', $user->regional_id)
        ->get();

    $approver = User::whereIn('role',['kepala_ro','admin'])->get();

    return view('fmea-demo', compact('schedule','teknisi','approver'));
    if ($schedule->segment->regional_id != Auth::user()->regional_id) {
    abort(403, 'Tidak punya akses ke schedule ini');
}
}

public function info(Request $request)
{
    $user = Auth::user();

    $query = Segment::with(['schedules' => function ($q) use ($request) {

        $q->where('status','approved')
  ->with('inspeksiHeader');

// 🔥 FILTER STATUS
if ($request->status) {

    if ($request->status == 'belum') {
        // belum ada inspeksi
        $q->whereDoesntHave('inspeksiHeader');
    } else {
        // sudah ada inspeksi dengan status tertentu
        $q->whereHas('inspeksiHeader', function ($q2) use ($request) {
            $q2->where('status_workflow', $request->status);
        });
    }
}

// 🔥 filter tanggal
if ($request->from) {
    $q->whereDate('planned_date', '>=', $request->from);
}

if ($request->to) {
    $q->whereDate('planned_date', '<=', $request->to);
}

// 🔥 SORTING
if ($request->sort == 'asc') {
    $q->orderBy('planned_date', 'asc');
} elseif ($request->sort == 'desc') {
    $q->orderBy('planned_date', 'desc');
} else {
    $q->orderBy('planned_date', 'asc');
}

    }])

    ->where('regional_id', $user->regional_id);

    // search
    if ($request->search) {
        $query->where('nama_segment', 'like', '%' . $request->search . '%');
    }

    // filter segment
    if ($request->segment) {
        $query->where('id', $request->segment);
    }

    $segments = $query->get();

    $allSegments = Segment::where('regional_id', $user->regional_id)->get();

    return view('maintenance.info', compact('segments','allSegments'));
}
}