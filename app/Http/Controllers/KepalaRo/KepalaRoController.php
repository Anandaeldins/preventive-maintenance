<?php

namespace App\Http\Controllers\KepalaRo;

use App\Http\Controllers\Controller;
use App\Models\InspeksiHeader;
use Illuminate\Support\Facades\Auth;
class KepalaRoController extends Controller
{
   public function dashboard()
{
    $user = Auth::user();

    // 🔥 FILTER GLOBAL BY REGIONAL
    $baseQuery = InspeksiHeader::whereHas('creator', function ($q) use ($user) {
        $q->where('regional_id', $user->regional_id);
    });

    // SUMMARY
    $totalReports = (clone $baseQuery)->count();

    $pendingRO = (clone $baseQuery)
        ->where('status_workflow', 'pending_ro')
        ->count();

    $pendingPusat = (clone $baseQuery)
        ->where('status_workflow', 'pending_pusat')
        ->count();

    $approvedReports = (clone $baseQuery)
        ->where('status_workflow', 'approved')
        ->count();

    // DATA TABLE
    $reports = InspeksiHeader::with('creator')
        ->where('status_workflow', 'pending_ro')
        ->whereHas('creator', function ($q) use ($user) {
            $q->where('regional_id', $user->regional_id);
        })
        ->latest()
        ->limit(5)
        ->get();

    return view('kepalaro.dashboard', compact(
        'totalReports',
        'pendingRO',
        'pendingPusat',
        'approvedReports',
        'reports'
    ));
}
}