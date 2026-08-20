<?php

namespace App\Http\Controllers;

use App\DTO\User\AddressDTO;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AddressController extends Controller
{
    public function __construct(
        protected AddressService $addressService
    ) {}
    public function index(string $storeSlug, Request $request): Response
    {
        $addresses = $this->addressService->getAddressesForUser($request->user()->id)
            ->map(fn (Address $a) => $this->format($a))
            ->values();

        return Inertia::render('Account/Addresses', [
            'addresses' => $addresses,
        ]);
    }

    public function store(string $storeSlug, Request $request): RedirectResponse
    {
        $dto = AddressDTO::fromRequest($request);
        $this->addressService->createAddress($request->user()->id, $dto);

        return back()->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function update(string $storeSlug, Request $request, int $id): RedirectResponse
    {
        $dto = AddressDTO::fromRequest($request);
        $this->addressService->updateAddress($request->user()->id, $id, $dto);

        return back()->with('success', 'Alamat diperbarui.');
    }

    public function destroy(string $storeSlug, Request $request, int $id): RedirectResponse
    {
        $this->addressService->deleteAddress($request->user()->id, $id);

        return back()->with('success', 'Alamat dihapus.');
    }

    public function makeDefault(string $storeSlug, Request $request, int $id): RedirectResponse
    {
        $this->addressService->makeDefault($request->user()->id, $id);

        return back()->with('success', 'Alamat utama diubah.');
    }



    /**
     * @return array<string, mixed>
     */
    private function format(Address $address): array
    {
        return [
            'id' => $address->id,
            'label' => $address->label,
            'recipient_name' => $address->recipient_name,
            'phone' => $address->phone,
            'address' => $address->address,
            'city' => $address->city,
            'district' => $address->district,
            'province' => $address->province,
            'postal_code' => $address->postal_code,
            'is_default' => (bool) $address->is_default,
        ];
    }
}
