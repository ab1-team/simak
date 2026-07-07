<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Usaha;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class LicenseController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $data = License::with('usaha')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('usaha', function ($row) {
                    if (!$row->usaha) {
                        return '-';
                    }
                    return $row->usaha->id . ' - ' . $row->usaha->nama_usaha;
                })
                ->editColumn('api_secret', function ($row) {
                    return $row->api_secret;
                })
                ->editColumn('is_active', function ($row) {
                    return $row->is_active
                        ? '<span class="badge badge-success">is_active</span>'
                        : '<span class="badge badge-secondary">nonactive</span>';
                })
                ->editColumn('expired_at', function ($row) {
                    if (!$row->expired_at) {
                        return '<span class="text-muted">Selamanya</span>';
                    }
                    if ($row->isExpired()) {
                        return '<span class="text-danger">' . $row->expired_at->format('d/m/Y H:i') . '</span>';
                    }
                    return $row->expired_at->format('d/m/Y H:i');
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-warning btn-edit" data-id="' . $row->id . '">Edit</button> '
                        . '<button class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '">Hapus</button>';
                })
                ->rawColumns(['is_active', 'expired_at', 'action'])
                ->make(true);
        }

        $usaha = Usaha::orderBy('nama_usaha')->get();
        $title = 'Manajemen Lisensi';

        return view('admin.license.index')->with(compact('title', 'usaha'));
    }

    public function store(Request $request)
    {
        $data = $request->only(['usaha_id', 'api_secret', 'is_active', 'expired_at']);

        $validate = Validator::make($data, [
            'usaha_id'   => 'required|exists:usaha,id|unique:licenses,usaha_id',
            'api_secret' => 'required|string|max:64|unique:licenses,api_secret',
            'is_active'  => 'nullable|boolean',
            'expired_at' => 'nullable|date',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        License::create([
            'usaha_id'   => $request->usaha_id,
            'api_secret' => $request->api_secret,
            'is_active'  => $request->boolean('is_active'),
            'expired_at' => $request->expired_at ?: null,
        ]);

        return response()->json([
            'success' => true,
            'msg'     => 'Lisensi berhasil ditambahkan.',
        ]);
    }

    public function show(License $license)
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'id'         => $license->id,
                'usaha_id'   => $license->usaha_id,
                'api_secret' => $license->api_secret,
                'is_active'  => (bool) $license->is_active,
                'expired_at' => $license->expired_at ? $license->expired_at->format('Y-m-d\TH:i') : null,
            ],
        ]);
    }

    public function update(Request $request, License $license)
    {
        $data = $request->only(['api_secret', 'is_active', 'expired_at']);

        $validate = Validator::make($data, [
            'api_secret' => 'required|string|max:64|unique:licenses,api_secret,' . $license->id,
            'is_active'  => 'nullable|boolean',
            'expired_at' => 'nullable|date',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $license->update([
            'api_secret' => $request->api_secret,
            'is_active'  => $request->boolean('is_active'),
            'expired_at' => $request->expired_at ?: null,
        ]);

        return response()->json([
            'success' => true,
            'msg'     => 'Lisensi berhasil diperbarui.',
        ]);
    }

    public function destroy(License $license)
    {
        $license->delete();

        return response()->json([
            'success' => true,
            'msg'     => 'Lisensi berhasil dihapus.',
        ]);
    }
}