<?php

namespace Database\Seeders;

use App\Models\FmeaOutput;
use App\Models\InspeksiDetail;
use App\Models\InspeksiFmeaDetail;
use App\Models\InspeksiHeader;
use App\Models\InspeksiKondisiUmum;
use App\Models\PmSchedule;
use App\Models\Regional;
use App\Models\Segment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class DebugScenarioSeeder extends Seeder
{
    /**
     * Seed debug-ready dummy scenarios for faster manual QA.
     */
    public function run(): void
    {
        $today = Carbon::today();

        $this->seedTaskReadySchedules($today);
        $this->seedTaskDateBoundaryScenarios($today);
        $this->seedInspectionScenarios($today);
        $this->seedFmeaOutputSnapshots($today);
    }

    private function seedTaskReadySchedules(Carbon $today): void
    {
        $taskReadyByRegional = [
            'Bekasi' => 'Backbone Cyber - MTH',
            'Jabatim' => 'Backbone Intiland - Dharma Lautan Utama',
            'Jakarta' => 'Backbone Cyber - HH Cyber',
            'Cilegon' => 'Backbone Bojanegara - Anyer',
            'Lampung' => 'Backbone Kalianda - Labuan Maringgai',
        ];

        $offset = 1;
        foreach ($taskReadyByRegional as $regional => $segmentName) {
            $this->upsertSchedule([
                'marker' => "[DEBUG][TASK-READY][{$regional}]",
                'regional' => $regional,
                'segment' => $segmentName,
                'planned_date' => $today->copy()->addDays($offset)->toDateString(),
                'priority' => 'RENDAH',
                'status' => 'approved',
            ]);
            $offset++;
        }
    }

    /**
     * Scenario debug untuk Task Maintenance:
     * 1) Jadwal lewat tanggal + belum dikerjakan => auto reject (besok disabled).
     * 2) Jadwal bulan lalu (bahkan kalau draft) => harus hilang di Task bulan sekarang.
     */
    private function seedTaskDateBoundaryScenarios(Carbon $today): void
    {
        $taskByRegional = [
            'Bekasi' => 'Backbone Cyber - MTH',
            'Jabatim' => 'Backbone Intiland - Dharma Lautan Utama',
            'Jakarta' => 'Backbone Cyber - HH Cyber',
            'Cilegon' => 'Backbone Bojanegara - Anyer',
            'Lampung' => 'Backbone Kalianda - Labuan Maringgai',
        ];

        $overdueDate = $today->copy()->subDay(); // contoh: 22 Apr tidak dikerjakan, 23 Apr auto reject
        $previousMonthDate = $today->copy()->subMonthNoOverflow()->day(21);

        foreach ($taskByRegional as $regional => $segmentName) {
            // Skenario 1: overdue tanpa inspeksi (harus auto-reject oleh logic app)
            $overdueSchedule = $this->upsertSchedule([
                'marker' => "[DEBUG][TASK][AUTO-REJECT][{$regional}]",
                'regional' => $regional,
                'segment' => $segmentName,
                'planned_date' => $overdueDate->toDateString(),
                'priority' => 'RENDAH',
                'status' => 'approved',
            ]);

            if ($overdueSchedule) {
                // pastikan benar-benar "belum dikerjakan"
                InspeksiHeader::where('schedule_id', $overdueSchedule->id)->delete();
            }

            // Skenario 2: bulan lalu + sudah draft (sebelumnya biasanya disabled), sekarang harus hilang
            $previousMonthSchedule = $this->upsertSchedule([
                'marker' => "[DEBUG][TASK][PREV-MONTH-HIDDEN][{$regional}]",
                'regional' => $regional,
                'segment' => $segmentName,
                'planned_date' => $previousMonthDate->toDateString(),
                'priority' => 'RENDAH',
                'status' => 'approved',
            ]);

            if ($previousMonthSchedule) {
                $teknisi = $previousMonthSchedule->teknisi1;

                InspeksiHeader::updateOrCreate(
                    ['schedule_id' => $previousMonthSchedule->id],
                    [
                        'segment_inspeksi' => $previousMonthSchedule->segment?->nama_segment,
                        'jalur_fo' => $previousMonthSchedule->segment?->jalur,
                        'nama_pelaksana' => $teknisi?->username,
                        'driver' => 'Driver Debug Prev Month',
                        'cara_patroli' => 'mobil',
                        'tanggal_inspeksi' => $previousMonthSchedule->planned_date?->toDateString(),
                        'prepared_by' => $teknisi?->id ?? $previousMonthSchedule->created_by,
                        'status_workflow' => 'draft',
                        'prepared_signature' => null,
                        'approved_signature' => null,
                        'priority' => null,
                        'schedule_pm' => null,
                    ]
                );
            }
        }
    }

    private function seedInspectionScenarios(Carbon $today): void
    {
        $scenarios = [
            [
                'marker' => '[DEBUG][INSPEKSI][DRAFT-NO-TEMUAN]',
                'regional' => 'Jabatim',
                'segment' => 'Backbone Intiland - MCS Ngagel',
                'planned_date' => $today->copy()->subDays(1)->toDateString(),
                'workflow' => 'draft',
                'cara_patroli' => 'mobil',
                'driver' => 'Driver Jabatim',
                'detail_states' => [],
                'fmea' => [],
                'kondisi_umum' => [
                    'marker_post' => 'baik',
                    'hand_hole' => 'baik',
                    'aksesoris_ku' => 'baik',
                    'jc_odp' => 'baik',
                ],
            ],
            [
                'marker' => '[DEBUG][INSPEKSI][PENDING-RO-RENDAH]',
                'regional' => 'Bekasi',
                'segment' => 'Akses PLGD - Nettocyber',
                'planned_date' => $today->copy()->subDays(2)->toDateString(),
                'workflow' => 'pending_ro',
                'cara_patroli' => 'motor',
                'driver' => 'Driver Bekasi',
                'detail_states' => [
                    'kabel_expose' => [
                        'status' => ['status' => 'ada', 'pelindung' => 'utuh', 'lingkungan' => 'aman'],
                        'catatan' => 'Expose ringan, perlu monitor rutin.',
                    ],
                ],
                'fmea' => [
                    ['item' => 'kabel_expose', 'severity' => 2, 'occurrence' => 2, 'detection' => 2],
                ],
                'kondisi_umum' => [
                    'marker_post' => 'baik',
                    'hand_hole' => 'baik',
                    'aksesoris_ku' => 'baik',
                    'jc_odp' => 'rusak',
                ],
            ],
            [
                'marker' => '[DEBUG][INSPEKSI][PENDING-PUSAT-SEDANG]',
                'regional' => 'Cilegon',
                'segment' => 'Backbone Bojanegara - Lingkar Selatan',
                'planned_date' => $today->copy()->subDays(3)->toDateString(),
                'workflow' => 'pending_pusat',
                'cara_patroli' => 'jalan_kaki',
                'driver' => 'Driver Cilegon',
                'detail_states' => [
                    'penyangga' => [
                        'status' => ['status' => 'rusak', 'kondisi' => 'retak', 'kabel' => 'menurun'],
                        'catatan' => 'Penyangga retak pada titik crossing.',
                    ],
                    'lingkungan' => [
                        'status' => ['status' => 'tidak_aman', 'dampak' => 'potensi'],
                        'catatan' => 'Ada aktivitas proyek dekat jalur.',
                    ],
                ],
                'fmea' => [
                    ['item' => 'penyangga', 'severity' => 3, 'occurrence' => 3, 'detection' => 3],
                    ['item' => 'lingkungan', 'severity' => 4, 'occurrence' => 4, 'detection' => 4],
                ],
                'kondisi_umum' => [
                    'marker_post' => 'rusak',
                    'hand_hole' => 'baik',
                    'aksesoris_ku' => 'baik',
                    'jc_odp' => 'baik',
                ],
            ],
            [
                'marker' => '[DEBUG][INSPEKSI][APPROVED-KRITIS]',
                'regional' => 'Lampung',
                'segment' => 'Backbone Labuan Maringgai - Tebanggi Besar',
                'planned_date' => $today->copy()->subDays(4)->toDateString(),
                'workflow' => 'approved',
                'cara_patroli' => 'mobil',
                'driver' => 'Driver Lampung',
                'detail_states' => [
                    'kabel_putus' => [
                        'status' => ['status' => 'ya', 'backup' => 'tidak', 'dampak' => 'down'],
                        'catatan' => 'Kabel putus total, layanan terganggu.',
                    ],
                    'tiang' => [
                        'status' => ['posisi' => 'miring', 'kondisi' => 'parah', 'miring' => 'berat'],
                        'catatan' => 'Tiang utama miring berat.',
                    ],
                ],
                'fmea' => [
                    ['item' => 'kabel_putus', 'severity' => 5, 'occurrence' => 5, 'detection' => 5],
                    ['item' => 'tiang', 'severity' => 4, 'occurrence' => 4, 'detection' => 4],
                ],
                'kondisi_umum' => [
                    'marker_post' => 'rusak',
                    'hand_hole' => 'rusak',
                    'aksesoris_ku' => 'baik',
                    'jc_odp' => 'rusak',
                ],
            ],
            [
                'marker' => '[DEBUG][INSPEKSI][REJECTED-SEDANG]',
                'regional' => 'Jakarta',
                'segment' => 'Akses BWI - Cyber',
                'planned_date' => $today->copy()->subDays(5)->toDateString(),
                'workflow' => 'rejected',
                'cara_patroli' => 'lainnya',
                'cara_patroli_lainnya' => 'inspeksi malam',
                'driver' => 'Driver Jakarta',
                'detail_states' => [
                    'clamp' => [
                        'status' => ['status' => 'rusak', 'kondisi' => 'tertekan'],
                        'catatan' => 'Clamp tertekan akibat tarikan kabel.',
                    ],
                    'vegetasi' => [
                        'status' => ['status' => 'tidak_aman', 'jarak' => 'tekan'],
                        'catatan' => 'Vegetasi menekan kabel pada span tengah.',
                    ],
                ],
                'fmea' => [
                    ['item' => 'clamp', 'severity' => 4, 'occurrence' => 3, 'detection' => 3],
                    ['item' => 'vegetasi', 'severity' => 4, 'occurrence' => 4, 'detection' => 4],
                ],
                'kondisi_umum' => [
                    'marker_post' => 'baik',
                    'hand_hole' => 'rusak',
                    'aksesoris_ku' => 'rusak',
                    'jc_odp' => 'baik',
                ],
            ],
        ];

        foreach ($scenarios as $scenario) {
            $schedule = $this->upsertSchedule([
                'marker' => $scenario['marker'],
                'regional' => $scenario['regional'],
                'segment' => $scenario['segment'],
                'planned_date' => $scenario['planned_date'],
                'priority' => 'RENDAH',
                'status' => 'approved',
            ]);

            if (!$schedule) {
                continue;
            }

            $regional = Regional::where('nama_regional', $scenario['regional'])->first();
            if (!$regional) {
                continue;
            }

            $teknisi = $this->regionalUser($regional->id, 'teknisi');
            $kepalaRo = $this->regionalUser($regional->id, 'kepala_ro');
            $pusat = User::where('role', 'pusat')->first();

            $maxRpn = $this->maxRpn($scenario['fmea']);
            [$priority, $schedulePm] = $this->priorityFromRpn($maxRpn);

            $approvedBy = null;
            if ($scenario['workflow'] === 'pending_pusat' || $scenario['workflow'] === 'rejected') {
                $approvedBy = $kepalaRo?->id;
            } elseif ($scenario['workflow'] === 'approved') {
                $approvedBy = $pusat?->id ?? $kepalaRo?->id;
            }

            $inspeksi = InspeksiHeader::updateOrCreate(
                ['schedule_id' => $schedule->id],
                [
                    'segment_inspeksi' => $schedule->segment?->nama_segment,
                    'jalur_fo' => $schedule->segment?->jalur,
                    'nama_pelaksana' => $teknisi?->username,
                    'driver' => $scenario['driver'],
                    'cara_patroli' => $scenario['cara_patroli'],
                    'cara_patroli_lainnya' => Arr::get($scenario, 'cara_patroli_lainnya'),
                    'tanggal_inspeksi' => $schedule->planned_date?->toDateString(),
                    'priority' => $priority,
                    'schedule_pm' => $schedulePm,
                    'prepared_by' => $teknisi?->id ?? $schedule->created_by,
                    'approved_by' => $approvedBy,
                    'prepared_signature' => $scenario['workflow'] === 'draft'
                        ? null
                        : 'data:image/png;base64,DEBUG_SIGNATURE',
                    'approved_signature' => $approvedBy
                        ? 'data:image/png;base64,DEBUG_APPROVAL'
                        : null,
                    'status_workflow' => $scenario['workflow'],
                ]
            );

            InspeksiDetail::where('inspeksi_id', $inspeksi->id)->delete();
            InspeksiFmeaDetail::where('inspeksi_id', $inspeksi->id)->delete();
            InspeksiKondisiUmum::where('inspeksi_id', $inspeksi->id)->delete();

            foreach ($scenario['detail_states'] as $objek => $detail) {
                InspeksiDetail::create([
                    'inspeksi_id' => $inspeksi->id,
                    'objek' => $objek,
                    'status' => json_encode($detail['status']),
                    'catatan' => $detail['catatan'] ?? null,
                ]);
            }

            foreach ($scenario['fmea'] as $row) {
                $rpn = $row['severity'] * $row['occurrence'] * $row['detection'];
                InspeksiFmeaDetail::create([
                    'inspeksi_id' => $inspeksi->id,
                    'item' => $row['item'],
                    'severity' => $row['severity'],
                    'occurrence' => $row['occurrence'],
                    'detection' => $row['detection'],
                    'rpn' => $rpn,
                    'risk_index' => round($rpn / 125, 2),
                ]);
            }

            InspeksiKondisiUmum::create([
                'inspeksi_id' => $inspeksi->id,
                'marker_post' => Arr::get($scenario, 'kondisi_umum.marker_post', 'baik'),
                'hand_hole' => Arr::get($scenario, 'kondisi_umum.hand_hole', 'baik'),
                'aksesoris_ku' => Arr::get($scenario, 'kondisi_umum.aksesoris_ku', 'baik'),
                'jc_odp' => Arr::get($scenario, 'kondisi_umum.jc_odp', 'baik'),
                'catatan_marker_post' => "Debug {$scenario['workflow']} - marker post",
                'catatan_hand_hole' => "Debug {$scenario['workflow']} - hand hole",
                'catatan_aksesoris_ku' => "Debug {$scenario['workflow']} - aksesoris",
                'catatan_jc_odp' => "Debug {$scenario['workflow']} - jc odp",
            ]);
        }
    }

    private function seedFmeaOutputSnapshots(Carbon $today): void
    {
        $snapshots = [
            [
                'segment' => 'Backbone Labuan Maringgai - Tebanggi Besar',
                'bulan' => $today->month,
                'tahun' => $today->year,
                'avg_rpn' => 125,
            ],
            [
                'segment' => 'Backbone Bojanegara - Lingkar Selatan',
                'bulan' => $today->month,
                'tahun' => $today->year,
                'avg_rpn' => 64,
            ],
            [
                'segment' => 'Akses PLGD - Nettocyber',
                'bulan' => $today->month,
                'tahun' => $today->year,
                'avg_rpn' => 8,
            ],
            [
                'segment' => 'Backbone Cyber - MTH',
                'bulan' => $today->copy()->subMonth()->month,
                'tahun' => $today->copy()->subMonth()->year,
                'avg_rpn' => 18,
            ],
        ];

        foreach ($snapshots as $snapshot) {
            $segment = Segment::where('nama_segment', $snapshot['segment'])->first();
            if (!$segment) {
                continue;
            }

            [$priority] = $this->priorityFromRpn((int) $snapshot['avg_rpn']);

            FmeaOutput::updateOrCreate(
                [
                    'segment_id' => $segment->id,
                    'bulan' => $snapshot['bulan'],
                    'tahun' => $snapshot['tahun'],
                ],
                [
                    'avg_rpn' => $snapshot['avg_rpn'],
                    'risk_index' => round($snapshot['avg_rpn'] / 125, 2),
                    'priority' => $priority ?? 'RENDAH',
                ]
            );
        }
    }

    private function upsertSchedule(array $payload): ?PmSchedule
    {
        $regional = Regional::where('nama_regional', $payload['regional'])->first();
        if (!$regional) {
            return null;
        }

        $segment = Segment::where('nama_segment', $payload['segment'])
            ->where('regional_id', $regional->id)
            ->first();
        if (!$segment) {
            return null;
        }

        $teknisi = $this->regionalUser($regional->id, 'teknisi');
        $kepalaRo = $this->regionalUser($regional->id, 'kepala_ro');
        $admin = User::where('role', 'admin')->first();

        $createdBy = $teknisi?->id ?? $admin?->id;
        if (!$createdBy) {
            return null;
        }

        return PmSchedule::updateOrCreate(
            ['notes' => $payload['marker']],
            [
                'segment_id' => $segment->id,
                'planned_date' => $payload['planned_date'],
                'priority' => $payload['priority'],
                'status' => $payload['status'],
                'created_by' => $createdBy,
                'approved_by' => in_array($payload['status'], ['approved', 'rejected'], true)
                    ? ($kepalaRo?->id ?? $admin?->id)
                    : null,
                'teknisi_1' => $teknisi?->id,
                'teknisi_2' => null,
                'signature_teknisi' => null,
                'signature_ro' => null,
                'notes' => $payload['marker'],
            ]
        );
    }

    private function regionalUser(int $regionalId, string $role): ?User
    {
        return User::where('role', $role)
            ->where('regional_id', $regionalId)
            ->first();
    }

    private function maxRpn(array $fmea): ?int
    {
        if (empty($fmea)) {
            return null;
        }

        return collect($fmea)
            ->map(fn ($row) => $row['severity'] * $row['occurrence'] * $row['detection'])
            ->max();
    }

    private function priorityFromRpn(?int $maxRpn): array
    {
        if (!$maxRpn) {
            return [null, null];
        }

        if ($maxRpn >= 100) {
            return ['KRITIS', 'minimal pm 3x sebulan'];
        }

        if ($maxRpn >= 50) {
            return ['SEDANG', 'minimal pm 2x sebulan'];
        }

        return ['RENDAH', 'minimal pm 1x sebulan'];
    }
}
