<?php

namespace App\Http\Controllers;

use App\Services\StoreService;
use App\Services\TaxReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaxReportController extends Controller
{
    public function __construct(
        protected TaxReportService $taxReportService,
        protected StoreService $storeService,
    ) {}

    public function index(Request $request): Response
    {
        $store = $this->storeService->getActiveStore($request->user()->id);
        $year = (int) $request->integer('year', now()->year);
        $month = $request->filled('month') ? (int) $request->integer('month') : null;

        $report = $store
            ? $this->taxReportService->report($store, $year, $month)
            : ['rows' => [], 'totals' => ['dpp' => 0, 'ppn' => 0, 'total' => 0], 'year' => $year, 'month' => $month];

        return Inertia::render('TaxReports/Index', [
            'report' => $report,
            'store' => $store ? [
                'is_pkp' => (bool) $store->is_pkp,
                'npwp' => $store->npwp,
                'tax_name' => $store->tax_name,
            ] : null,
            'ppn_rate' => (float) config('tax.ppn_rate', 11),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $store = $this->storeService->getActiveStore($request->user()->id);
        abort_unless($store, 404);

        $year = (int) $request->integer('year', now()->year);
        $month = $request->filled('month') ? (int) $request->integer('month') : null;
        $format = $request->string('format')->toString() === 'xls' ? 'xls' : 'csv';

        return $format === 'xls'
            ? $this->taxReportService->exportExcel($store, $year, $month)
            : $this->taxReportService->exportCsv($store, $year, $month);
    }
}
