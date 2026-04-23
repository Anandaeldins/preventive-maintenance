<?php

namespace App\Http\Controllers;
use App\Models\InspeksiKondisiUmum;
use App\Models\InspeksiHeader;
use App\Models\PmSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\InspeksiDetail;
use App\Models\InspeksiImage;
use App\Http\Controllers\Teknisi\FmeaController;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;


class InspeksiController extends Controller
{
    /**
     * Store new inspection - enforces schedule requirement and sets workflow status
     */
public function store(Request $request)
{
    $uploadErrors = $this->collectUploadErrors($request);
    if (!empty($uploadErrors)) {
        throw ValidationException::withMessages($uploadErrors);
    }

    $request->validate([
        'segment_inspeksi' => 'required|string|max:150',
        'tanggal_inspeksi' => 'required|date',
        'schedule_id' => 'required|exists:pm_schedules,id',
        'images' => 'nullable|array|max:10',
        'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
    ], [
        'images.array' => 'Format upload foto tidak valid.',
        'images.max' => 'Maksimal 10 foto per laporan.',
        'images.*.uploaded' => 'Foto gagal di-upload. Cek ukuran file dan coba lagi.',
        'images.*.image' => 'File upload harus berupa gambar.',
        'images.*.mimes' => 'Format foto hanya boleh JPG, JPEG, atau PNG.',
        'images.*.max' => 'Ukuran foto maksimal 5 MB per file.',
    ]);


    $schedule = PmSchedule::findOrFail($request->schedule_id);
    $isSubmitToRo = $request->action === 'submit_ro';

    $status = $isSubmitToRo
        ? 'pending_ro'
        : 'draft';

    try {

        DB::transaction(function () use ($request, $schedule, $status, $isSubmitToRo) {

            // ================= INIT =================
            $results = [];

            $jalurFO = $request->jalur_fo ?? 'non_backbone';

            $adjustSeverity = function ($S) use ($jalurFO) {
                return ($jalurFO === 'backbone') ? min($S + 1, 5) : $S;
            };

           $hitung = function($item, $S, $O, $D) use (&$results) {

    $RPN = $S * $O * $D;

    // ✅ MAX GLOBAL FMEA
    $maxRPN = 125;

    $index = $RPN / $maxRPN;

    $results[] = [
        'item' => $item,
        'S' => $S,
        'O' => $O,
        'D' => $D,
        'RPN' => $RPN,
        'index' => round($index, 2)
    ];
};

            // ================= 7 ITEM =================

            if(strtolower($request->input('kabel_putus.status','')) === 'ya'){
                $S = match ($request->input('kabel_putus.dampak')) {
                    'down' => 5,
                    'sebagian' => 4,
                    'normal' => 3,
                    default => 2
                };
                $S = $adjustSeverity($S);
                $D = $request->input('kabel_putus.backup') === 'ada' ? 2 : 1;
                $O = $this->hitungOccurrence($request->segment_inspeksi,'kabel_putus','status','ya');
                $hitung('kabel_putus', $S, $O, $D);
            }

            if(strtolower($request->input('kabel_expose.status','')) === 'ada'){
                $S = match ($request->input('kabel_expose.pelindung')) {
                    'rusak' => 4,
                    'retak' => 3,
                    'utuh' => 2,
                    default => 1
                };
                $S = $adjustSeverity($S);
                $D = match ($request->input('kabel_expose.lingkungan')) {
                    'beban' => 3,
                    'tanah_air' => 2,
                    'aman' => 1,
                    default => 2
                };
                $O = $this->hitungOccurrence($request->segment_inspeksi,'kabel_expose','status','ada');
                $hitung('kabel_expose', $S, $O, $D);
            }

            if(strtolower($request->input('penyangga.status','')) === 'rusak'){
                $S = match ($request->input('penyangga.kondisi')) {
                    'lepas' => 4,
                    'retak' => 3,
                    'karat' => 2,
                    default => 1
                };
                $S = $adjustSeverity($S);
                $D = match ($request->input('penyangga.kabel')) {
                    'tertarik' => 3,
                    'menurun' => 2,
                    'aman' => 1,
                    default => 2
                };
                $O = $this->hitungOccurrence($request->segment_inspeksi,'penyangga','status','rusak');
                $hitung('penyangga', $S, $O, $D);
            }

            if(strtolower($request->input('tiang.posisi','')) === 'miring'){
                $S = match ($request->input('tiang.miring')) {
                    'berat' => 4,
                    'sedang' => 3,
                    'ringan' => 2,
                    default => 1
                };
                $S = $adjustSeverity($S);
                $D = $request->input('tiang.kondisi') === 'parah' ? 2 : 1;
                $O = $this->hitungOccurrence($request->segment_inspeksi,'tiang','posisi','miring');
                $hitung('tiang', $S, $O, $D);
            }

            if(strtolower($request->input('clamp.status','')) === 'rusak'){
                $S = match ($request->input('clamp.kondisi')) {
                    'tertekan' => 4,
                    'tergesek' => 3,
                    'kendur' => 2,
                    default => 1
                };
                $S = $adjustSeverity($S);
                $D = 2;
                $O = $this->hitungOccurrence($request->segment_inspeksi,'clamp','status','rusak');
                $hitung('clamp', $S, $O, $D);
            }

            if(strtolower($request->input('lingkungan.status','')) === 'tidak_aman'){
                $S = match ($request->input('lingkungan.dampak')) {
                    'sudah' => 4,
                    'potensi' => 3,
                    'belum' => 2,
                    default => 1
                };
                $S = $adjustSeverity($S);
                $D = 3;
                $O = $this->hitungOccurrence($request->segment_inspeksi,'lingkungan','status','tidak_aman');
                $hitung('lingkungan', $S, $O, $D);
            }

            if(strtolower($request->input('vegetasi.status','')) === 'tidak_aman'){
                $S = match ($request->input('vegetasi.jarak')) {
                    'tumbang' => 4,
                    'tekan' => 3,
                    'sentuh' => 2,
                    'dekat' => 1,
                    default => 1
                };
                $S = $adjustSeverity($S);
                $D = 3;
                $O = $this->hitungOccurrence($request->segment_inspeksi,'vegetasi','status','tidak_aman');
                $hitung('vegetasi', $S, $O, $D);
            }

            if ($isSubmitToRo && count($results) === 0) {
                throw ValidationException::withMessages([
                    'kondisi' => 'Minimal ada satu kondisi temuan untuk kirim ke Kepala RO.',
                ]);
            }

            // ================= PRIORITAS =================
            $priority = null;
            $schedulePm = null;
            if (count($results) > 0) {
                $maxIndex = collect($results)->max('index');

                if ($maxIndex >= 0.8) {
                    $priority = 'KRITIS';
                    $schedulePm = 'minimal pm 3x sebulan';
                } elseif ($maxIndex >= 0.4) {
                    $priority = 'SEDANG';
                    $schedulePm = 'minimal pm 2x sebulan';
                } else {
                    $priority = 'RENDAH';
                    $schedulePm = 'minimal pm 1x sebulan';
                }
            }

            if ($isSubmitToRo && !$request->filled('signature_teknisi')) {
                throw ValidationException::withMessages([
                    'signature_teknisi' => 'Tanda tangan wajib diisi untuk kirim ke Kepala RO.',
                ]);
            }

            // ================= SIMPAN HEADER =================
            $inspeksi = InspeksiHeader::create([
                'segment_inspeksi' => $request->segment_inspeksi,
                'jalur_fo' => $request->jalur_fo,
                'nama_pelaksana' => $request->nama_pelaksana,
                'driver' => $request->driver,
                'cara_patroli' => $request->cara_patroli,
                'cara_patroli_lainnya' => $request->cara_patroli_lainnya,
                'tanggal_inspeksi' => $request->tanggal_inspeksi,
                'priority' => $priority,
                'schedule_pm' => $schedulePm,
                'prepared_by' => Auth::id(),
                'approved_by' => $request->approved_by,
                'prepared_signature' => $request->signature_teknisi ?: null,
                'approved_signature' => $request->approved_canvas,
                'schedule_id' => $schedule->id,
                'status_workflow' => $status
            ]);

            // ================= SIMPAN DETAIL =================
            foreach (['kabel_putus','kabel_expose','penyangga','tiang','clamp','lingkungan','vegetasi'] as $obj) {
                if ($request->has($obj)) {
                    $inspeksi->details()->create([
                        'objek' => $obj,
                        'status' => json_encode($request->$obj),
                        'catatan' => data_get($request->input('kondisi', []), "{$obj}.catatan")

                    ]);
                }
            }

            // ================= SIMPAN FMEA =================
            if (count($results) > 0) {
                foreach ($results as $r) {
                    $inspeksi->fmeaDetails()->create([
                        'item' => $r['item'],
                        'severity' => $r['S'],
                        'occurrence' => $r['O'],
                        'detection' => $r['D'],
                        'rpn' => $r['RPN'],
                        'risk_index' => $r['index'],
                    ]);
                }
            }
            // ================= SIMPAN KONDISI UMUM =================
            InspeksiKondisiUmum::create([
                'inspeksi_id' => $inspeksi->id,
                'marker_post' => $request->marker_post,
                'hand_hole' => $request->hand_hole,
                'aksesoris_ku' => $request->aksesoris_ku,
                'jc_odp' => $request->jc_odp,

                'catatan_marker_post' => $request->kondisi['marker_post']['catatan'] ?? null,
                'catatan_hand_hole' => $request->kondisi['hand_hole']['catatan'] ?? null,
                'catatan_aksesoris_ku' => $request->kondisi['aksesoris_ku']['catatan'] ?? null,
                'catatan_jc_odp' => $request->kondisi['jc_odp']['catatan'] ?? null,
            ]);
                        // ================== 🔥 TAMBAH INI ==================
            $uploadedImages = $request->file('images');
            if (is_array($uploadedImages)) {
                foreach ($uploadedImages as $index => $file) {
                    if (!$file->isValid()) {
                        throw ValidationException::withMessages([
                            "images.$index" => 'Foto gagal di-upload: ' . $this->translateUploadError($file->getError()),
                        ]);
                    }

                    $path = $file->store('inspeksi_images', 'public');

                    InspeksiImage::create([
                        'inspeksi_header_id' => $inspeksi->id,
                        'image_path' => $path
                    ]);
                }
            }
                
    


        });

        $successMessage = $isSubmitToRo
            ? 'Laporan berhasil dikirim ke Kepala RO.'
            : 'Draft laporan berhasil disimpan.';

        return redirect()->route('tasks.index')->with('success', $successMessage);

    } catch (ValidationException $e) {
        throw $e;
    } catch (\Exception $e) {
        return back()
            ->withInput()
            ->with('error', 'Gagal menyimpan laporan: ' . $e->getMessage());
    }
}
private function hitungOccurrence($segment, $objek, $field, $value)
{
    $inspeksiIds = InspeksiHeader::whereRaw('LOWER(segment_inspeksi) = ?', [strtolower($segment)])
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->pluck('id');

    $details = InspeksiDetail::whereIn('inspeksi_id', $inspeksiIds)
        ->where('objek', $objek)
        ->get();

    $jumlah = 0;

    foreach ($details as $d) {
        $status = json_decode($d->status, true);
        if (isset($status[$field]) && $status[$field] == $value) {
            $jumlah++;
        }
    }

    if ($jumlah >= 5) return 5;
    if ($jumlah >= 3) return 4;
    if ($jumlah >= 2) return 3;
    if ($jumlah == 1) return 2;

    return 1;
}

private function translateUploadError(int $errorCode): string
{
    return match ($errorCode) {
        UPLOAD_ERR_INI_SIZE => 'Ukuran file melebihi batas upload server (upload_max_filesize).',
        UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas form.',
        UPLOAD_ERR_PARTIAL => 'File hanya ter-upload sebagian. Coba upload ulang.',
        UPLOAD_ERR_NO_FILE => 'Tidak ada file yang ter-upload.',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary upload tidak ditemukan di server.',
        UPLOAD_ERR_CANT_WRITE => 'Server gagal menulis file ke disk.',
        UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP.',
        default => 'Terjadi kesalahan upload yang tidak diketahui.',
    };
}

private function collectUploadErrors(Request $request): array
{
    $messages = [];

    $files = $request->file('images');
    if (is_array($files)) {
        foreach ($files as $index => $file) {
            if ($file && method_exists($file, 'isValid') && !$file->isValid()) {
                if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $messages["images.$index"] = sprintf(
                    'Foto #%d gagal di-upload: %s (code: %d).',
                    $index + 1,
                    $this->translateUploadError($file->getError()),
                    $file->getError()
                );
            }
        }
    }

    $rawErrorList = $_FILES['images']['error'] ?? null;
    if (is_array($rawErrorList)) {
        foreach ($rawErrorList as $index => $rawCode) {
            $errorCode = (int) $rawCode;
            if ($errorCode === UPLOAD_ERR_OK || $errorCode === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $key = "images.$index";
            if (!isset($messages[$key])) {
                $messages[$key] = sprintf(
                    'Foto #%d gagal di-upload: %s (code: %d).',
                    $index + 1,
                    $this->translateUploadError($errorCode),
                    $errorCode
                );
            }
        }
    }

    if (!empty($messages)) {
        Log::warning('Upload inspeksi gagal di level PHP', [
            'errors' => array_values($messages),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_file_uploads' => ini_get('max_file_uploads'),
            'upload_tmp_dir' => ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
            'content_length' => $request->server('CONTENT_LENGTH'),
        ]);
    }

    return $messages;
}


    /**
     * Submit inspection for RO approval (draft → pending_ro)
     */
    public function submitForApproval($id)
    {
        $inspeksi = InspeksiHeader::findOrFail($id);

        if (!$this->isDraftOwner($inspeksi)) {
            return back()->with('error', 'Anda tidak punya akses untuk mengirim draft ini.');
        }
        
        // Validate current status
        if ($inspeksi->status_workflow !== 'draft') {
            return back()->with('error', 'Inspeksi tidak dapat dikirim untuk approval dalam status saat ini.');
        }

        // Check if has schedule
        if (!$inspeksi->schedule_id) {
            return back()->with('error', 'Inspeksi harus terhubung dengan jadwal PM yang disetujui.');
        }

        // Update status to pending_ro
        $inspeksi->update(['status_workflow' => 'pending_ro']);

        return back()->with('success', 'Inspeksi berhasil dikirim untuk approval.');
    }

    public function destroyDraft($id)
    {
        $inspeksi = InspeksiHeader::with('images')->findOrFail($id);

        if (!$this->isDraftOwner($inspeksi)) {
            return back()->with('error', 'Anda tidak punya akses untuk menghapus draft ini.');
        }

        if ($inspeksi->status_workflow !== 'draft') {
            return back()->with('error', 'Hanya laporan berstatus draft yang bisa dihapus.');
        }

        foreach ($inspeksi->images as $image) {
            if ($image->image_path) {
                Storage::disk('public')->delete($image->image_path);
            }
        }

        $inspeksi->delete();

        return back()->with('success', 'Draft laporan berhasil dihapus.');
    }

    public function updateDraft(Request $request, $id)
    {
        $inspeksi = InspeksiHeader::with(['kondisiUmum', 'details', 'fmeaDetails', 'images'])->findOrFail($id);

        if (!$this->isDraftOwner($inspeksi)) {
            return back()->with('error', 'Anda tidak punya akses untuk mengedit draft ini.');
        }

        if ($inspeksi->status_workflow !== 'draft') {
            return back()->with('error', 'Hanya laporan berstatus draft yang bisa diedit.');
        }

        $uploadErrors = $this->collectUploadErrors($request);
        if (!empty($uploadErrors)) {
            throw ValidationException::withMessages($uploadErrors);
        }

        $request->validate([
            'segment_inspeksi' => 'required|string|max:150',
            'tanggal_inspeksi' => 'required|date',
            'schedule_id' => 'required|exists:pm_schedules,id',
            'images' => 'nullable|array|max:10',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'images.array' => 'Format upload foto tidak valid.',
            'images.max' => 'Maksimal 10 foto per laporan.',
            'images.*.uploaded' => 'Foto gagal di-upload. Cek ukuran file dan coba lagi.',
            'images.*.image' => 'File upload harus berupa gambar.',
            'images.*.mimes' => 'Format foto hanya boleh JPG, JPEG, atau PNG.',
            'images.*.max' => 'Ukuran foto maksimal 5 MB per file.',
        ]);

        $schedule = PmSchedule::findOrFail($request->schedule_id);
        if ((int) $inspeksi->schedule_id !== (int) $schedule->id) {
            return back()->with('error', 'Draft tidak sesuai dengan jadwal PM yang dipilih.');
        }

        $isSubmitToRo = $request->action === 'submit_ro';
        $status = $isSubmitToRo ? 'pending_ro' : 'draft';

        try {
            DB::transaction(function () use ($request, $inspeksi, $schedule, $status, $isSubmitToRo) {
                $results = [];

                $jalurFO = $request->jalur_fo ?? $inspeksi->jalur_fo ?? 'non_backbone';

                $adjustSeverity = function ($S) use ($jalurFO) {
                    return ($jalurFO === 'backbone') ? min($S + 1, 5) : $S;
                };

                $hitung = function ($item, $S, $O, $D) use (&$results) {
                    $RPN = $S * $O * $D;
                    $maxRPN = 125;
                    $index = $RPN / $maxRPN;

                    $results[] = [
                        'item' => $item,
                        'S' => $S,
                        'O' => $O,
                        'D' => $D,
                        'RPN' => $RPN,
                        'index' => round($index, 2)
                    ];
                };

                if (strtolower($request->input('kabel_putus.status', '')) === 'ya') {
                    $S = match ($request->input('kabel_putus.dampak')) {
                        'down' => 5,
                        'sebagian' => 4,
                        'normal' => 3,
                        default => 2
                    };
                    $S = $adjustSeverity($S);
                    $D = $request->input('kabel_putus.backup') === 'ada' ? 2 : 1;
                    $O = $this->hitungOccurrence($request->segment_inspeksi, 'kabel_putus', 'status', 'ya');
                    $hitung('kabel_putus', $S, $O, $D);
                }

                if (strtolower($request->input('kabel_expose.status', '')) === 'ada') {
                    $S = match ($request->input('kabel_expose.pelindung')) {
                        'rusak' => 4,
                        'retak' => 3,
                        'utuh' => 2,
                        default => 1
                    };
                    $S = $adjustSeverity($S);
                    $D = match ($request->input('kabel_expose.lingkungan')) {
                        'beban' => 3,
                        'tanah_air' => 2,
                        'aman' => 1,
                        default => 2
                    };
                    $O = $this->hitungOccurrence($request->segment_inspeksi, 'kabel_expose', 'status', 'ada');
                    $hitung('kabel_expose', $S, $O, $D);
                }

                if (strtolower($request->input('penyangga.status', '')) === 'rusak') {
                    $S = match ($request->input('penyangga.kondisi')) {
                        'lepas' => 4,
                        'retak' => 3,
                        'karat' => 2,
                        default => 1
                    };
                    $S = $adjustSeverity($S);
                    $D = match ($request->input('penyangga.kabel')) {
                        'tertarik' => 3,
                        'menurun' => 2,
                        'aman' => 1,
                        default => 2
                    };
                    $O = $this->hitungOccurrence($request->segment_inspeksi, 'penyangga', 'status', 'rusak');
                    $hitung('penyangga', $S, $O, $D);
                }

                if (strtolower($request->input('tiang.posisi', '')) === 'miring') {
                    $S = match ($request->input('tiang.miring')) {
                        'berat' => 4,
                        'sedang' => 3,
                        'ringan' => 2,
                        default => 1
                    };
                    $S = $adjustSeverity($S);
                    $D = $request->input('tiang.kondisi') === 'parah' ? 2 : 1;
                    $O = $this->hitungOccurrence($request->segment_inspeksi, 'tiang', 'posisi', 'miring');
                    $hitung('tiang', $S, $O, $D);
                }

                if (strtolower($request->input('clamp.status', '')) === 'rusak') {
                    $S = match ($request->input('clamp.kondisi')) {
                        'tertekan' => 4,
                        'tergesek' => 3,
                        'kendur' => 2,
                        default => 1
                    };
                    $S = $adjustSeverity($S);
                    $D = 2;
                    $O = $this->hitungOccurrence($request->segment_inspeksi, 'clamp', 'status', 'rusak');
                    $hitung('clamp', $S, $O, $D);
                }

                if (strtolower($request->input('lingkungan.status', '')) === 'tidak_aman') {
                    $S = match ($request->input('lingkungan.dampak')) {
                        'sudah' => 4,
                        'potensi' => 3,
                        'belum' => 2,
                        default => 1
                    };
                    $S = $adjustSeverity($S);
                    $D = 3;
                    $O = $this->hitungOccurrence($request->segment_inspeksi, 'lingkungan', 'status', 'tidak_aman');
                    $hitung('lingkungan', $S, $O, $D);
                }

                if (strtolower($request->input('vegetasi.status', '')) === 'tidak_aman') {
                    $S = match ($request->input('vegetasi.jarak')) {
                        'tumbang' => 4,
                        'tekan' => 3,
                        'sentuh' => 2,
                        'dekat' => 1,
                        default => 1
                    };
                    $S = $adjustSeverity($S);
                    $D = 3;
                    $O = $this->hitungOccurrence($request->segment_inspeksi, 'vegetasi', 'status', 'tidak_aman');
                    $hitung('vegetasi', $S, $O, $D);
                }

                if ($isSubmitToRo && count($results) === 0) {
                    throw ValidationException::withMessages([
                        'kondisi' => 'Minimal ada satu kondisi temuan untuk kirim ke Kepala RO.',
                    ]);
                }

                $priority = null;
                $schedulePm = null;
                if (count($results) > 0) {
                    $maxIndex = collect($results)->max('index');

                    if ($maxIndex >= 0.8) {
                        $priority = 'KRITIS';
                        $schedulePm = 'minimal pm 3x sebulan';
                    } elseif ($maxIndex >= 0.4) {
                        $priority = 'SEDANG';
                        $schedulePm = 'minimal pm 2x sebulan';
                    } else {
                        $priority = 'RENDAH';
                        $schedulePm = 'minimal pm 1x sebulan';
                    }
                }

                if ($isSubmitToRo && !$request->filled('signature_teknisi') && !$inspeksi->prepared_signature) {
                    throw ValidationException::withMessages([
                        'signature_teknisi' => 'Tanda tangan wajib diisi untuk kirim ke Kepala RO.',
                    ]);
                }

                $preparedSignature = $request->filled('signature_teknisi')
                    ? $request->signature_teknisi
                    : $inspeksi->prepared_signature;

                $inspeksi->update([
                    'segment_inspeksi' => $request->segment_inspeksi,
                    'jalur_fo' => $request->jalur_fo,
                    'nama_pelaksana' => $request->nama_pelaksana,
                    'driver' => $request->driver,
                    'cara_patroli' => $request->cara_patroli,
                    'cara_patroli_lainnya' => $request->cara_patroli_lainnya,
                    'tanggal_inspeksi' => $request->tanggal_inspeksi,
                    'priority' => $priority,
                    'schedule_pm' => $schedulePm,
                    'approved_by' => $request->approved_by,
                    'prepared_signature' => $preparedSignature,
                    'approved_signature' => $request->approved_canvas,
                    'schedule_id' => $schedule->id,
                    'status_workflow' => $status,
                ]);

                $inspeksi->details()->delete();
                foreach (['kabel_putus','kabel_expose','penyangga','tiang','clamp','lingkungan','vegetasi'] as $obj) {
                    if ($request->has($obj)) {
                        $inspeksi->details()->create([
                            'objek' => $obj,
                            'status' => json_encode($request->$obj),
                            'catatan' => data_get($request->input('kondisi', []), "{$obj}.catatan"),
                        ]);
                    }
                }

                $inspeksi->fmeaDetails()->delete();
                foreach ($results as $r) {
                    $inspeksi->fmeaDetails()->create([
                        'item' => $r['item'],
                        'severity' => $r['S'],
                        'occurrence' => $r['O'],
                        'detection' => $r['D'],
                        'rpn' => $r['RPN'],
                        'risk_index' => $r['index'],
                    ]);
                }

                InspeksiKondisiUmum::updateOrCreate(
                    ['inspeksi_id' => $inspeksi->id],
                    [
                        'marker_post' => $request->marker_post,
                        'hand_hole' => $request->hand_hole,
                        'aksesoris_ku' => $request->aksesoris_ku,
                        'jc_odp' => $request->jc_odp,
                        'catatan_marker_post' => $request->kondisi['marker_post']['catatan'] ?? null,
                        'catatan_hand_hole' => $request->kondisi['hand_hole']['catatan'] ?? null,
                        'catatan_aksesoris_ku' => $request->kondisi['aksesoris_ku']['catatan'] ?? null,
                        'catatan_jc_odp' => $request->kondisi['jc_odp']['catatan'] ?? null,
                    ]
                );

                $uploadedImages = $request->file('images');
                $newImages = collect(is_array($uploadedImages) ? $uploadedImages : [])
                    ->filter(function ($file) {
                        return $file
                            && method_exists($file, 'isValid')
                            && $file->isValid()
                            && $file->getError() === UPLOAD_ERR_OK;
                    })
                    ->values();

                // Jika user upload foto baru saat edit draft:
                // hapus semua foto lama lalu ganti full dengan foto baru.
                if ($newImages->isNotEmpty()) {
                    foreach ($inspeksi->images as $oldImage) {
                        if ($oldImage->image_path) {
                            Storage::disk('public')->delete($oldImage->image_path);
                        }
                    }

                    $inspeksi->images()->delete();

                    foreach ($newImages as $file) {
                        $path = $file->store('inspeksi_images', 'public');
                        InspeksiImage::create([
                            'inspeksi_header_id' => $inspeksi->id,
                            'image_path' => $path,
                        ]);
                    }
                }
            });

            $successMessage = $isSubmitToRo
                ? 'Draft berhasil diperbarui dan dikirim ke Kepala RO.'
                : 'Draft laporan berhasil diperbarui.';

            return redirect()->route('maintenance.info')->with('success', $successMessage);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui draft: ' . $e->getMessage());
        }
    }

    public function riskSummary()
    {
        $segments = InspeksiHeader::with('fmeaDetails')
            ->select('segment_inspeksi')
            ->groupBy('segment_inspeksi')
            ->get()
            ->map(function ($segment) {
                $inspeksi = InspeksiHeader::where('segment_inspeksi', $segment->segment_inspeksi)
                    ->with('fmeaDetails')
                    ->latest()
                    ->first();

                return [
                    'segment' => $segment->segment_inspeksi,
                    'risk_summary' => $inspeksi ? $inspeksi->risk_summary : null,
                    'last_inspection' => $inspeksi ? $inspeksi->tanggal_inspeksi : null,
                ];
            });

        return view('admin.risk-summary', compact('segments'));
    }

    /**
     * Display inspection reports created by the logged-in technician
     */
    public function myReports()
    {
        // Get only reports created by the logged-in technician
        $reports = InspeksiHeader::where('prepared_by', Auth::id())
            ->with(['pmSchedule', 'approvals'])
            ->orderBy('tanggal_inspeksi', 'desc')
            ->get();

        return view('inspeksi.my-reports', compact('reports'));
    }

  public function approveByRo(Request $request,$id)
{
    $report = InspeksiHeader::with('creator')->findOrFail($id);

    // 🔒 VALIDASI REGIONAL
    if ($report->creator->regional_id !== Auth::user()->regional_id) {
        return back()->with('error','Tidak boleh approve laporan beda regional.');
    }

    $report->update([
        'approved_signature' => $request->signature_ro,
        'approved_by' => Auth::id(),
        'status_workflow' => 'pending_pusat'
    ]);

    return back()->with('success','Report disetujui oleh Kepala RO.');
}

public function rejectByRo(Request $request,$id)
{

$report = InspeksiHeader::findOrFail($id);

$report->update([

'status_workflow'=>'rejected'

]);

return back()->with('success','Report ditolak oleh Kepala RO.');

}

public function approveByPusat($id)
{
    $report = InspeksiHeader::findOrFail($id);

    if ($report->status_workflow !== 'pending_pusat') {
        return back()->with('error','Report tidak dalam status pending pusat.');
    }

    // update status
    $report->update([
        'status_workflow' => 'approved'
    ]);

    // =============================
    // 🔥 TRIGGER FMEA OTOMATIS
    // =============================
  FmeaController::generateFromInspeksi(
    $report->segment_inspeksi,
    $report->tanggal_inspeksi
);
    

    return back()->with('success','Report disetujui oleh pusat & FMEA dihitung.');
}


public function rejectByPusat($id)
{
    $report = InspeksiHeader::findOrFail($id);

    $report->update([
        'status_workflow' => 'rejected'
    ]);

    return back()->with('success','Report ditolak oleh pusat.');
}

public function pendingRO()
{
    $user = Auth::user();

    $reports = InspeksiHeader::where('status_workflow','pending_ro')
        ->whereHas('creator', function($q) use ($user) {
            $q->where('regional_id', $user->regional_id);
        })
        ->with(['pmSchedule.segment', 'creator.regional'])
        ->latest()
        ->get();

    return view('approval.ro-reports', compact('reports'));
}



public function pendingPusat(Request $request)
{
    $query = InspeksiHeader::where('status_workflow','pending_pusat')
        ->with(['pmSchedule.segment', 'creator.regional']);

    // ✅ FILTER REGIONAL
    if ($request->regional) {
        $query->whereHas('creator.regional', function ($q) use ($request) {
            $q->where('id', $request->regional);
        });
    }

    $reports = $query->latest()->get();

    // ✅ ambil data regional buat dropdown
    $regionals = \App\Models\Regional::all();

    return view('approval.pusat-reports', compact('reports','regionals'));
}

public function modal($id)
{
    $report = InspeksiHeader::with([
        'pmSchedule.segment',
        'kondisiUmum',
        'fmeaDetails',
        'details'
    ])->findOrFail($id);

    return view('inspeksi.modal-report', compact('report'));
}

private function isDraftOwner(InspeksiHeader $inspeksi): bool
{
    $user = Auth::user();
    if (!$user) {
        return false;
    }

    return (string) $inspeksi->prepared_by === (string) $user->id
        || (string) $inspeksi->prepared_by === (string) $user->username;
}
}
