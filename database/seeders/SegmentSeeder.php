<?php

namespace Database\Seeders;

use App\Models\Regional;
use App\Models\Segment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SegmentSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $segmentsByRegional = [
            'Bekasi' => [
                'Backbone Cyber - MTH',
                'Backbone Cyber - Tegal Parang',
                'Akses PLGD - Nettocyber',
                'Akses Wika - TVONE',
            ],
            'Jabatim' => [
                'Backbone Intiland - Dharma Lautan Utama',
                'Backbone Intiland - MCS Ngagel',
                'Backbone Intiland - Apartemen Taman Melati',
                'Akses HH-002 - PGN Pemuda',
            ],
            'Jakarta' => [
                'Backbone Cyber - HH Cyber',
                'Backbone Cyber - PGN Serpong',
                'Backbone Equatra - HH A18',
                'Akses BWI - Cyber',
            ],
            'Cilegon' => [
                'Backbone Bojanegara - Anyer',
                'Backbone Bojanegara - Lingkar Selatan',
                'Akses Bojanegara - Bitung',
                'Akses Bojonegara - Kronjo',
            ],
            'Lampung' => [
                'Backbone Labuan Maringgai - Tebanggi Besar',
                'Backbone Kalianda - Labuan Maringgai',
                'Akses Core HH61 - Gedung Usuludin',
                'Akses Core HH61 - Gedung Tarbyah',
            ],
        ];

        foreach ($segmentsByRegional as $regionalName => $segments) {
            $regional = Regional::where('nama_regional', $regionalName)->first();
            if (!$regional) {
                continue;
            }

            foreach ($segments as $segmentName) {
                $jalur = Str::startsWith($segmentName, 'Backbone') ? 'backbone' : 'non_backbone';

                $segment = Segment::where('nama_segment', $segmentName)
                    ->where('regional_id', $regional->id)
                    ->first();

                if (!$segment) {
                    Segment::create([
                        'nama_segment' => $segmentName,
                        'kode_segment' => $this->generateUniqueKodeSegment(),
                        'jalur' => $jalur,
                        'regional_id' => $regional->id,
                    ]);
                    continue;
                }

                $segment->update([
                    'jalur' => $jalur,
                    'regional_id' => $regional->id,
                ]);
            }
        }
    }

    private function generateUniqueKodeSegment(): string
    {
        do {
            $kode = 'SEG-' . strtoupper(Str::random(6));
        } while (Segment::where('kode_segment', $kode)->exists());

        return $kode;
    }
}
