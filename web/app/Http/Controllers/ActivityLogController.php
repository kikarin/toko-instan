<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function __construct(protected ActivityLogService $logService) {}

    public function index(Request $request): Response
    {
        $logs = $this->logService->getLogsForUser($request->user()->id, 20);

        return Inertia::render('ActivityLog/Index', [
            'logs' => collect($logs->items())->map(fn ($log) => [
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
