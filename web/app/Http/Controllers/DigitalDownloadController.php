<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Models\OrderItem;
use App\Repositories\StoreRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DigitalDownloadController extends Controller
{
    public function __construct(
        protected StoreRepository $storeRepository
    ) {}

    public function download(Request $request, string $storeSlug, int $orderItemId): StreamedResponse
    {
        $store = $this->storeRepository->findBySlug($storeSlug);
        if (! $store) {
            abort(404);
        }

        $item = OrderItem::query()
            ->with(['order', 'order.store'])
            ->whereKey($orderItemId)
            ->firstOrFail();

        $user = $request->user();
        $order = $item->order;

        if (! $order || (int) $order->store_id !== (int) $store->id) {
            abort(404);
        }

        if ($order->customer_email !== $user->email) {
            abort(403, 'Akses ditolak');
        }

        if (! in_array($order->status, ['paid', 'processing', 'packed', 'shipped', 'completed'], true)) {
            abort(403, 'Unduhan tersedia setelah pembayaran dikonfirmasi.');
        }

        if (($item->product_type ?? ProductType::Physical->value) !== ProductType::Digital->value) {
            abort(404, 'Item ini bukan produk digital.');
        }

        $path = $item->digital_file_path;
        if (! filled($path) || ! Storage::disk('r2')->exists($path)) {
            abort(404, 'File digital tidak ditemukan.');
        }

        $filename = $item->digital_file_name ?: basename($path);

        return Storage::disk('r2')->download($path, $filename);
    }
}
