<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AddressController extends Controller
{
    public function index(Request $request): Response
    {
        $addresses = $request->user()->addresses()
            ->orderByDesc('is_default')
            ->orderBy('created_at')
            ->get()
            ->map(fn (Address $a) => $this->format($a))
            ->values();

        return Inertia::render('Account/Addresses', [
            'addresses' => $addresses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAddress($request);
        $user = $request->user();

        if ((bool) ($validated['is_default'] ?? false) || $user->addresses()->count() === 0) {
            $user->addresses()->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        $user->addresses()->create($validated);

        return back()->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $this->validateAddress($request);
        $address = $this->findAddress($request, $id);

        if ($validated['is_default'] ?? false) {
            $request->user()->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return back()->with('success', 'Alamat diperbarui.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $address = $this->findAddress($request, $id);
        $address->delete();

        return back()->with('success', 'Alamat dihapus.');
    }

    public function makeDefault(Request $request, int $id): RedirectResponse
    {
        $request->user()->addresses()->update(['is_default' => false]);
        $this->findAddress($request, $id)->update(['is_default' => true]);

        return back()->with('success', 'Alamat utama diubah.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'label' => 'nullable|string|max:60',
            'recipient_name' => 'required|string|max:120',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'is_default' => 'nullable|boolean',
        ]);
    }

    private function findAddress(Request $request, int $id): Address
    {
        return $request->user()->addresses()->findOrFail($id);
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
            'province' => $address->province,
            'postal_code' => $address->postal_code,
            'is_default' => (bool) $address->is_default,
        ];
    }
}
