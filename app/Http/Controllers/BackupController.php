<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function __construct(
        protected BackupService $backupService
    ) {}

    /**
     * Download structured ZIP backup of all user data.
     * GET /settings/backup/export
     */
    public function export(Request $request): BinaryFileResponse
    {
        $user = $request->user();
        $zipPath = $this->backupService->export($user);
        $filename = 'hybrid20-backup-' . date('Y-m-d_His') . '.zip';

        return response()->download($zipPath, $filename, [
            'Content-Type' => 'application/zip',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Inspect and preview uploaded ZIP backup before committing.
     * POST /settings/backup/preview
     */
    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'backup_file' => 'required|file|max:51200',
        ], [
            'backup_file.required' => 'Pilih berkas ZIP cadangan terlebih dahulu.',
            'backup_file.max' => 'Ukuran berkas maksimal adalah 50 MB.',
        ]);

        try {
            $summary = $this->backupService->preview($request->file('backup_file'));
            return response()->json($summary);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Atomically restore backup into user's database.
     * POST /settings/backup/import
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => 'required|file|max:51200',
            'confirm_replace' => 'accepted',
        ], [
            'backup_file.required' => 'Pilih berkas ZIP cadangan terlebih dahulu.',
            'backup_file.max' => 'Ukuran berkas maksimal adalah 50 MB.',
            'confirm_replace.accepted' => 'Kamu harus mencentang persetujuan bahwa data yang ada akan diganti.',
        ]);

        try {
            $this->backupService->import($request->file('backup_file'), $request->user());

            return redirect()->route('settings.edit')->with('success', 'Data cadangan berhasil dipulihkan.');
        } catch (Exception $e) {
            return redirect()->route('settings.edit')
                ->withErrors(['backup_file' => 'Gagal memulihkan cadangan: ' . $e->getMessage()]);
        }
    }
}
