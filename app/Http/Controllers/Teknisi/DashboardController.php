<?php

namespace App\Http\Controllers\Teknisi;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\InspeksiHeader;

class DashboardController extends Controller
{
   public function index()
{
    $userId = Auth::id();

    // 🔥 AMBIL TASK DARI INSPEKSI HEADER
    $tasks = InspeksiHeader::where('teknisi_id', $userId)
        ->latest()
        ->get();

    // 🔥 SUMMARY
    $totalTask = $tasks->count();

    $doneTask = $tasks->where('status_workflow', 'approved')->count();

    $pendingTask = $tasks->whereIn('status_workflow', [
        'draft',
        'pending_ro',
        'pending_pusat'
    ])->count();

    $highRisk = $tasks->where('priority', 'KRITIS')->count();

    return view('teknisi.dashboard', compact(
        'tasks',
        'totalTask',
        'doneTask',
        'pendingTask',
        'highRisk'
    ));
}
}