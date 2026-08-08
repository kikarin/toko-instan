<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Store;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $store = Store::whereHas(
            'tenant',
            fn ($q) => $q->where('user_id', $request->user()->id)
        )->first();

        $logs = $store
            ? ActivityLog::with('user')
                ->where('tenant_id', $store->tenant_id)
                ->latest('created_at')
                ->paginate(20)
            : collect();

        return Inertia::render('ActivityLog/Index', [
            'logs' => $logs->map(fn (ActivityLog $log) => [
                'id' => $log->id,
                'action' => $log->action,
                'action_label' => $this->actionLabel($log->action),
                'subject_type' => class_basename((string) $log->subject_type),
                'subject_id' => $log->subject_id,
                'properties' => $log->properties,
                'user' => $log->user?->name,
                'ip' => $log->ip,
                'created_at' => $log->created_at?->translatedFormat('d M Y, H:i'),
            ]),
        ]);
    }

    protected function actionLabel(string $action): string
    {
        return match ($action) {
            'created' => 'dibuat',
            'updated' => 'diubah',
            'deleted' => 'dihapus',
            'stock_in' => 'stok masuk',
            'stock_out' => 'stok keluar',
            'stock_adjustment' => 'stok disesuaikan',
            'order_status' => 'status pesanan diubah',
            'withdrawal_request' => 'mengajukan penarikan',
            'withdrawal_status' => 'status penarikan diubah',
            'store_updated' => 'pengaturan toko diubah',
            default => $action,
        };
    }
}
