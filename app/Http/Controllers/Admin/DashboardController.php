<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\InspeksiHeader;
class DashboardController extends Controller
{
   public function index()
{
    $totalUser = User::count();

    $totalLaporan = InspeksiHeader::count();

    $pending = InspeksiHeader::whereIn('status_workflow', [
        'pending_ro',
        'pending_pusat'
    ])->count();

    $approved = InspeksiHeader::where('status_workflow', 'approved')->count();
// 🔥 ambil 7 laporan terbaru
    $latestReports = InspeksiHeader::with(['preparer.regional'])
        ->latest()
        ->limit(7)
        ->get();

    return view('admin.admin-d', compact(
        'totalUser',
        'totalLaporan',
        'pending',
        'approved',
        'latestReports'
    ));
}
}