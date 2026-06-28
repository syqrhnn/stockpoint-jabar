<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notifikasi;
use Carbon\Carbon;

class NotifikasiController extends Controller
{
    /**
     * Tampilkan halaman riwayat notifikasi
     */
    public function viewIndex()
    {
        return view('notifikasi.index');
    }

    /**
     * Membangun query dasar notifikasi berdasarkan role user
     */
    private function getBaseQuery(Request $request)
    {
        $role = $request->session()->get('role');
        $gudang_id = $request->session()->get('gudang_id');

        $query = Notifikasi::query();

        if (in_array($role, ['kepala_gudang', 'staf_gudang'])) {
            $query->where('gudang_id', $gudang_id);
        }

        return $query;
    }

    /**
     * GET daftar notifikasi (API)
     */
    public function getRiwayat(Request $request)
    {
        $query = $this->getBaseQuery($request);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $notifikasi = $query->orderBy('created_at', 'desc')->paginate(20);

        // Format waktu untuk frontend
        $notifikasi->getCollection()->transform(function ($notif) {
            $notif->waktu_relatif = Carbon::parse($notif->created_at)->diffForHumans();
            return $notif;
        });

        return response()->json([
            'success' => true,
            'data' => $notifikasi
        ]);
    }

    /**
     * GET count notifikasi belum dibaca (API)
     */
    public function getUnread(Request $request)
    {
        $query = $this->getBaseQuery($request);
        $count = $query->where('status', 'belum_dibaca')->count();

        // Ambil juga 5 notifikasi terbaru untuk dropdown
        $queryLatest = $this->getBaseQuery($request);
        $latest = $queryLatest->orderBy('created_at', 'desc')->limit(5)->get();
        
        $latest->transform(function ($notif) {
            $notif->waktu_relatif = Carbon::parse($notif->created_at)->diffForHumans();
            return $notif;
        });

        return response()->json([
            'success' => true,
            'unread_count' => $count,
            'latest' => $latest
        ]);
    }

    /**
     * PATCH tandai satu notifikasi sudah dibaca (API)
     */
    public function markAsRead(Request $request, $id)
    {
        $query = $this->getBaseQuery($request);
        $notif = $query->where('id', $id)->first();

        if ($notif) {
            $notif->update(['status' => 'sudah_dibaca']);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Notifikasi tidak ditemukan'], 404);
    }

    /**
     * PATCH tandai semua notifikasi sudah dibaca (API)
     */
    public function markAllAsRead(Request $request)
    {
        $query = $this->getBaseQuery($request);
        $query->where('status', 'belum_dibaca')->update(['status' => 'sudah_dibaca']);

        return response()->json(['success' => true]);
    }
}
