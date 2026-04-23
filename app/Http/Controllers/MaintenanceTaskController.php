<?php

namespace App\Http\Controllers;

use App\Models\InspeksiHeader;
use App\Models\PmSchedule;
use App\Models\Segment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceTaskController extends Controller
{
    public function index()
    {
        PmSchedule::rejectOverdueWithoutInspeksi();

        $userId = Auth::id();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $segments = Segment::with(['schedules' => function ($query) use ($userId, $monthStart, $monthEnd) {
            $query->whereIn('status', ['approved', 'rejected'])
                ->whereBetween('planned_date', [$monthStart, $monthEnd])
                ->where(function ($q) use ($userId) {
                    $q->where('teknisi_1', $userId)
                        ->orWhere('teknisi_2', $userId);
                })
                ->with('inspeksiHeader')
                ->orderBy('planned_date');
        }])->get();

        return view('task.index', compact('segments'));
    }

    public function show(Request $request, $schedule)
    {
        $schedule = PmSchedule::with('segment')->findOrFail($schedule);
        $user = Auth::user();

        $teknisi = User::where('role', 'teknisi')
            ->where('regional_id', $user->regional_id)
            ->get();

        $approver = User::whereIn('role', ['kepala_ro', 'admin'])->get();

        if ($schedule->segment->regional_id != $user->regional_id) {
            abort(403, 'Tidak punya akses ke schedule ini');
        }

        $draft = null;
        $draftPayload = null;

        if ($request->filled('draft_id')) {
            $draft = InspeksiHeader::with(['details', 'kondisiUmum', 'images'])->findOrFail($request->draft_id);

            if ((int) $draft->schedule_id !== (int) $schedule->id) {
                abort(404, 'Draft tidak sesuai dengan jadwal.');
            }

            $isOwner = (string) $draft->prepared_by === (string) $user->id
                || (string) $draft->prepared_by === (string) $user->username;

            if (!$isOwner) {
                abort(403, 'Tidak punya akses ke draft ini.');
            }

            if ($draft->status_workflow !== 'draft') {
                abort(403, 'Hanya draft yang bisa diedit.');
            }

            $draftPayload = $this->buildDraftPayload($draft);
        }

        return view('fmea-demo', compact('schedule', 'teknisi', 'approver', 'draft', 'draftPayload'));
    }

    public function info(Request $request)
    {
        PmSchedule::rejectOverdueWithoutInspeksi();

        $user = Auth::user();

        $query = Segment::with(['schedules' => function ($q) use ($request) {
            $q->whereIn('status', ['approved', 'rejected'])
                ->with(['inspeksiHeader.kondisiUmum']);

            if ($request->status) {
                if ($request->status === 'belum') {
                    $q->where('status', 'approved')
                        ->whereDoesntHave('inspeksiHeader');
                } elseif ($request->status === 'rejected') {
                    $q->where(function ($inner) {
                        $inner->whereHas('inspeksiHeader', function ($q2) {
                            $q2->where('status_workflow', 'rejected');
                        })->orWhere(function ($q2) {
                            $q2->where('status', 'rejected')
                                ->whereDoesntHave('inspeksiHeader');
                        });
                    });
                } else {
                    $q->whereHas('inspeksiHeader', function ($q2) use ($request) {
                        $q2->where('status_workflow', $request->status);
                    });
                }
            }

            if ($request->from) {
                $q->whereDate('planned_date', '>=', $request->from);
            }

            if ($request->to) {
                $q->whereDate('planned_date', '<=', $request->to);
            }

            if ($request->sort === 'asc') {
                $q->orderBy('planned_date', 'asc');
            } elseif ($request->sort === 'desc') {
                $q->orderBy('planned_date', 'desc');
            } else {
                $q->orderBy('planned_date', 'asc');
            }
        }])->where('regional_id', $user->regional_id);

        if ($request->search) {
            $query->where('nama_segment', 'like', '%' . $request->search . '%');
        }

        if ($request->segment) {
            $query->where('id', $request->segment);
        }

        $segments = $query->get();
        $allSegments = Segment::where('regional_id', $user->regional_id)->get();

        return view('maintenance.info', compact('segments', 'allSegments'));
    }

    private function buildDraftPayload(InspeksiHeader $draft): array
    {
        $payload = [
            'nama_pelaksana' => $draft->nama_pelaksana,
            'driver' => $draft->driver,
            'cara_patroli' => $draft->cara_patroli,
            'cara_patroli_lainnya' => $draft->cara_patroli_lainnya,
            'tanggal_inspeksi' => optional($draft->tanggal_inspeksi)->format('Y-m-d'),
            'marker_post' => optional($draft->kondisiUmum)->marker_post,
            'hand_hole' => optional($draft->kondisiUmum)->hand_hole,
            'aksesoris_ku' => optional($draft->kondisiUmum)->aksesoris_ku,
            'jc_odp' => optional($draft->kondisiUmum)->jc_odp,
            'signature_teknisi' => $draft->prepared_signature,
            'kondisi' => [
                'marker_post' => ['catatan' => optional($draft->kondisiUmum)->catatan_marker_post],
                'hand_hole' => ['catatan' => optional($draft->kondisiUmum)->catatan_hand_hole],
                'aksesoris_ku' => ['catatan' => optional($draft->kondisiUmum)->catatan_aksesoris_ku],
                'jc_odp' => ['catatan' => optional($draft->kondisiUmum)->catatan_jc_odp],
            ],
        ];

        foreach ($draft->details as $detail) {
            $status = json_decode($detail->status, true);
            if (is_array($status)) {
                $payload[$detail->objek] = $status;
            }

            $payload['kondisi'][$detail->objek]['catatan'] = $detail->catatan;
        }

        return $payload;
    }
}

