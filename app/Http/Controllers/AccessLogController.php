<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AccessLogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $logs = AccessLog::with('user')->select('access_logs.*')->orderByDesc('created_at');

            if ($request->filled('user_id')) {
                $logs->where('user_id', $request->user_id);
            }
            if ($request->filled('url')) {
                $logs->where('url', 'like', '%' . $request->url . '%');
            }

            return DataTables::of($logs)
                ->addColumn('user_name', function (AccessLog $log) {
                    return $log->user ? $log->user->name : '—';
                })
                ->addColumn('created_at_formatted', function (AccessLog $log) {
                    return $log->created_at?->format('d/m/Y H:i:s') ?? '—';
                })
                ->addColumn('url_short', function (AccessLog $log) {
                    $url = $log->url ?? '';
                    return strlen($url) > 60 ? substr($url, 0, 57) . '...' : $url;
                })
                ->make(true);
        }

        return view('access_logs.index');
    }
}
