<?php

namespace App\Http\Controllers\Pusat;

use App\Http\Controllers\Controller;
use App\Models\InspeksiHeader;

class DashboardController extends Controller
{
    public function dashboard()
    {
        // KPI
        $totalReports = InspeksiHeader::count();
        $pendingRO = InspeksiHeader::where('status_workflow', 'pending_ro')->count();
        $pendingPusat = InspeksiHeader::where('status_workflow', 'pending_pusat')->count();
        $approvedReports = InspeksiHeader::where('status_workflow', 'approved')->count();
        $approved = InspeksiHeader::where('status_workflow', 'approved')->count();
        // Grafik Regional
        $regionalData = InspeksiHeader::with('creator.regional')
            ->get()
            ->groupBy(fn($item) => $item->creator->regional->nama_regional ?? 'Tidak diketahui')
            ->map(fn($items) => count($items));

        $regionalLabels = $regionalData->keys();
        $regionalCounts = $regionalData->values();
        $latestReports = InspeksiHeader::with('creator.regional')
            ->latest()
            ->limit(10)
            ->get();
        // Table
        $reports = InspeksiHeader::with('creator.regional')
            ->latest()
            ->limit(10)
            ->get();

        return view('pusat.dashboard', compact(
            'totalReports',
            'pendingRO',
            'pendingPusat',
            'approved',
            'approvedReports',
            'regionalLabels',
            'regionalCounts',
            'reports',
            'latestReports'
        ));
    }
}