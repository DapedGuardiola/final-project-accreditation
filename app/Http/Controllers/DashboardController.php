<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var UserModel|null $user */
        $user = auth()->user();
        $role = $user ? $user->getRole() : null;
        $userId = $user ? $user->id_user : null;

        $tables = [
            'p_penelitian',
            'p_publikasi',
            'p_pengabdian',
            'p_sertifikasi',
            'p_hki',
            'p_karya_buku',
            'p_kegiatan',
            'p_organisasi',
            'p_prestasi',
            'p_profesi',
        ];

        $data = [
            'totalTerValidasi' => 0,
            'totalPerluValidasi' => 0,
            'totalTidakValid' => 0,
            'data' => []
        ];

        foreach ($tables as $table) {
            $query = DB::table($table)->where('status', 'Tervalidasi');
            if ($role === 'DOS' && $userId) {
                $query->where('id_user', $userId);
            }
            $tervalidasi = $query->count();

            $query = DB::table($table)->where('status', 'Perlu Validasi');
            if ($role === 'DOS' && $userId) {
                $query->where('id_user', $userId);
            }
            $perluValidasi = $query->count();

            $query = DB::table($table)->where('status', 'Tidak Valid');
            if ($role === 'DOS' && $userId) {
                $query->where('id_user', $userId);
            }
            $tidakValid = $query->count();

            $data['totalTerValidasi'] += $tervalidasi;
            $data['totalPerluValidasi'] += $perluValidasi;
            $data['totalTidakValid'] += $tidakValid;

            // Bungkus dalam data['data']
            $data['data'][substr($table, 2)] = [
                'Tervalidasi' => $tervalidasi,
                'Perlu Validasi' => $perluValidasi,
                'Tidak Valid' => $tidakValid,
            ];
        }
        return view('dashboard', compact('data'));
    }

    public function moreInfo(Request $request)
    {
        /** @var UserModel|null $user */
        $user = auth()->user();
        $role = $user ? $user->getRole() : null;
        $userId = $user ? $user->id_user : null;

        $status = strtolower($request->query('status'));

        $mappings = [
            'p_penelitian'   => ['id' => 'id_penelitian', 'nama' => 'judul_penelitian', 'jenis' => 'Penelitian', 'id_user' => 'id_user'],
            'p_publikasi'    => ['id' => 'id_publikasi', 'nama' => 'judul', 'jenis' => 'Publikasi', 'id_user' => 'id_user'],
            'p_pengabdian'   => ['id' => 'id_pengabdian', 'nama' => 'judul_pengabdian', 'jenis' => 'Pengabdian', 'id_user' => 'id_user'],
            'p_sertifikasi'  => ['id' => 'id_sertifikasi', 'nama' => 'nama_sertifikasi', 'jenis' => 'Sertifikasi', 'id_user' => 'id_user'],
            'p_hki'          => ['id' => 'id_hki', 'nama' => 'judul', 'jenis' => 'HKI', 'id_user' => 'id_user'],
            'p_karya_buku'   => ['id' => 'id_karya_buku', 'nama' => 'judul_buku', 'jenis' => 'Karya Buku', 'id_user' => 'id_user'],
            'p_kegiatan'     => ['id' => 'id_kegiatan', 'nama' => 'jenis_kegiatan', 'jenis' => 'Kegiatan', 'id_user' => 'id_user'],
            'p_organisasi'   => ['id' => 'id_organisasi', 'nama' => 'nama_organisasi', 'jenis' => 'Organisasi', 'id_user' => 'id_user'],
            'p_prestasi'     => ['id' => 'id_prestasi', 'nama' => 'prestasi_yang_dicapai', 'jenis' => 'Prestasi', 'id_user' => 'id_user'],
            'p_profesi'      => ['id' => 'id_profesi', 'nama' => 'perguruan_tinggi', 'jenis' => 'Profesi', 'id_user' => 'id_user'],
        ];

        $data = collect();

        foreach ($mappings as $table => $config) {
            $query = DB::table($table)
                ->leftJoin('profile_user', "{$table}.{$config['id_user']}", '=', 'profile_user.id_user')
                ->select([
                    "{$config['id']} as id",
                    "{$config['nama']} as nama",
                    "{$table}.status",
                    "{$table}.sumber_data as sumber",
                    DB::raw("'{$config['jenis']}' as jenis"),
                    'profile_user.nama_lengkap as nama_dosen'
                ]);

            if ($status) {
                $query->where("{$table}.status", $status);
            }
            if ($role === 'DOS' && $userId) {
                $query->where("{$table}.{$config['id_user']}", $userId);
            }
            $data = $data->merge($query->get());
        }
        return view('dashboard.index', compact('data', 'status'));
    }

}
