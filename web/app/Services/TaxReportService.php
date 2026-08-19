<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Store;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaxReportService
{
    /**
     * @return array{rows: list<array<string, mixed>>, totals: array{dpp: int, ppn: int, total: int}, year: int, month: ?int}
     */
    public function report(Store $store, int $year, ?int $month): array
    {
        $query = Order::query()
            ->where('store_id', $store->id)
            ->whereIn('status', ['paid', 'processing', 'packed', 'shipped', 'completed'])
            ->whereYear('created_at', $year)
            ->when($month, fn ($q) => $q->whereMonth('created_at', $month))
            ->orderBy('created_at');

        $rows = $query->get()->map(function (Order $order) {
            $shipping = (int) ($order->shipping_cost ?? 0);
            $discount = (int) ($order->discount ?? 0);
            $ppn = (int) ($order->tax ?? 0);
            $total = (int) round((float) $order->total_amount);
            $dpp = max(0, $total - $shipping - $ppn);

            return [
                'order_number' => $order->order_number,
                'date' => $order->created_at?->format('Y-m-d H:i'),
                'customer' => $order->customer_name,
                'dpp' => $dpp,
                'ppn' => $ppn,
                'shipping' => $shipping,
                'discount' => $discount,
                'total' => $total,
            ];
        })->values()->all();

        return [
            'rows' => $rows,
            'totals' => [
                'dpp' => (int) array_sum(array_column($rows, 'dpp')),
                'ppn' => (int) array_sum(array_column($rows, 'ppn')),
                'total' => (int) array_sum(array_column($rows, 'total')),
            ],
            'year' => $year,
            'month' => $month,
        ];
    }

    public function exportCsv(Store $store, int $year, ?int $month): StreamedResponse
    {
        $report = $this->report($store, $year, $month);
        $filename = $this->filename($store, $year, $month, 'csv');

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['No. Order', 'Tanggal', 'Pelanggan', 'DPP', 'PPN', 'Ongkir', 'Diskon', 'Total']);
            foreach ($report['rows'] as $row) {
                fputcsv($out, [
                    $row['order_number'],
                    $row['date'],
                    $row['customer'],
                    $row['dpp'],
                    $row['ppn'],
                    $row['shipping'],
                    $row['discount'],
                    $row['total'],
                ]);
            }
            fputcsv($out, ['TOTAL', '', '', $report['totals']['dpp'], $report['totals']['ppn'], '', '', $report['totals']['total']]);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportExcel(Store $store, int $year, ?int $month): StreamedResponse
    {
        $report = $this->report($store, $year, $month);
        $filename = $this->filename($store, $year, $month, 'xls');

        $escape = fn ($v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $cells = '';
        $header = ['No. Order', 'Tanggal', 'Pelanggan', 'DPP', 'PPN', 'Ongkir', 'Diskon', 'Total'];
        $cells .= '<Row>';
        foreach ($header as $h) {
            $cells .= '<Cell><Data ss:Type="String">'.$escape($h).'</Data></Cell>';
        }
        $cells .= '</Row>';

        foreach ($report['rows'] as $row) {
            $cells .= '<Row>';
            foreach (['order_number', 'date', 'customer'] as $key) {
                $cells .= '<Cell><Data ss:Type="String">'.$escape($row[$key]).'</Data></Cell>';
            }
            foreach (['dpp', 'ppn', 'shipping', 'discount', 'total'] as $key) {
                $cells .= '<Cell><Data ss:Type="Number">'.$row[$key].'</Data></Cell>';
            }
            $cells .= '</Row>';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
            .'<Worksheet ss:Name="Pajak"><Table>'.$cells.'</Table></Worksheet></Workbook>';

        return response()->streamDownload(function () use ($xml) {
            echo $xml;
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    protected function filename(Store $store, int $year, ?int $month, string $ext): string
    {
        $period = $month ? sprintf('%d-%02d', $year, $month) : (string) $year;

        return 'laporan-pajak-'.$store->slug.'-'.$period.'.'.$ext;
    }
}
