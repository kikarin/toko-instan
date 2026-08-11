<?php

namespace App\Repositories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AddressRepository
{
    /**
     * @return Collection<int, Address>
     */
    public function getAddressesForUser(int $userId): Collection
    {
        return Address::where('user_id', $userId)
            ->orderByDesc('is_default')
            ->get();
    }

    public function findAddressForUser(int $userId, int $addressId): ?Address
    {
        return Address::where('user_id', $userId)
            ->where('id', $addressId)
            ->first();
    }

    public function createAddressForUser(int $userId, array $data): Address
    {
        $user = User::findOrFail($userId);
        return $user->addresses()->create($data);
    }

    public function updateAddress(Address $address, array $data): bool
    {
        return $address->update($data);
    }

    public function setAsDefault(Address $address): void
    {
        // Unset previous defaults
        Address::where('user_id', $address->user_id)
            ->where('id', '!=', $address->id)
            ->update(['is_default' => false]);
            
        // Set new default
        $address->update(['is_default' => true]);
    }
    
    public function unsetAllDefaults(int $userId): void
    {
        Address::where('user_id', $userId)->update(['is_default' => false]);
    }

    public function deleteAddress(Address $address): ?bool
    {
        return $address->delete();
    }
}
