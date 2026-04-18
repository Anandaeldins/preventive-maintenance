<?php

namespace App\Http\Controllers\Teknisi;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\InspeksiHeader;
use Illuminate\Support\Facades\DB;
class DashboardController extends Controller
{
  public function index()
{
    $userId = Auth::id();

    // TASK
    $tasks = InspeksiHeader::where('prepared_by', $userId)
        ->latest()
        ->limit(5)
        ->get();

   

    // SUMMARY
    $totalTask = $tasks->count();

    $completedTask = $tasks->where('status_workflow', 'approved')->count();

    $pendingTask = $tasks->whereIn('status_workflow', [
        'draft',
        'pending_ro',
        'pending_pusat'
    ])->count();

$nextSchedule = DB::table('pm_schedules')
    ->join('segments', 'pm_schedules.segment_id', '=', 'segments.id')
    ->select(
        'pm_schedules.*',
        'segments.nama_segment'
    )
    ->where('pm_schedules.teknisi_1', $userId)
    ->where('pm_schedules.status', 'approved')
    ->whereDate('pm_schedules.planned_date', '>=', now())
    ->orderBy('pm_schedules.planned_date', 'asc')
    ->first();
  
$chartRaw = DB::table('pm_schedules')
    ->join('segments', 'pm_schedules.segment_id', '=', 'segments.id')

// 🔥 LEFT JOIN ke inspeksi
    ->leftJoin('inspeksi_headers', function ($join) use ($userId) {
        $join->on('pm_schedules.id', '=', 'inspeksi_headers.schedule_id')
             ->where('inspeksi_headers.prepared_by', $userId)
             ->where('inspeksi_headers.status_workflow', 'approved');
    })

    ->select(
        'segments.nama_segment as segment',
        DB::raw('COUNT(pm_schedules.planned_date) as total')
    )
    ->where('pm_schedules.teknisi_1', $userId)
    ->where('pm_schedules.status', 'approved')
    // 🔥 FILTER: cuma yang BELUM ada approved
    ->whereNull('inspeksi_headers.id')
    ->groupBy('segments.nama_segment')
    ->orderByDesc('total')
    ->get();

// TOP 5
$top = $chartRaw->take(5);

// OTHERS (sekali aja)
$othersTotal = $chartRaw->skip(5)->sum('total');

if ($othersTotal > 0) {
    $top->push((object)[
        'segment' => 'Others',
        'total' => $othersTotal
    ]);
}

// FINAL FORMAT
$labels = $top->pluck('segment');
$values = $top->pluck('total');

return view('teknisi.dashboard', compact(
    'tasks',
    'totalTask',
    'pendingTask',
    'completedTask',
    'labels',
    'values',
    'nextSchedule'
));
}
}