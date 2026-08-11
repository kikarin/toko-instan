<?php

namespace App\Services;

use App\DTO\User\AddressDTO;
use App\Models\Address;
use App\Repositories\AddressRepository;
use Illuminate\Database\Eloquent\Collection;

class AddressService
{
    public function __construct(
        protected AddressRepository $addressRepository
    ) {}

    /**
     * @return Collection<int, Address>
     */
    public function getAddressesForUser(int $userId): Collection
    {
        return $this->addressRepository->getAddressesForUser($userId);
    }

    public function createAddress(int $userId, AddressDTO $dto): Address
    {
        $data = $dto->validatedData;
        $addresses = $this->addressRepository->getAddressesForUser($userId);

        if (($data['is_default'] ?? false) || $addresses->isEmpty()) {
            $this->addressRepository->unsetAllDefaults($userId);
            $data['is_default'] = true;
        }

        return $this->addressRepository->createAddressForUser($userId, $data);
    }

    public function updateAddress(int $userId, int $addressId, AddressDTO $dto): void
    {
        $address = $this->addressRepository->findAddressForUser($userId, $addressId);
        
        if (! $address) {
            abort(404, 'Alamat tidak ditemukan');
        }

        $data = $dto->validatedData;

        if ($data['is_default'] ?? false) {
            $this->addressRepository->setAsDefault($address);
        }

        $this->addressRepository->updateAddress($address, $data);
    }

    public function deleteAddress(int $userId, int $addressId): void
    {
        $address = $this->addressRepository->findAddressForUser($userId, $addressId);

        if (! $address) {
            abort(404, 'Alamat tidak ditemukan');
        }

        $this->addressRepository->deleteAddress($address);
    }

    public function makeDefault(int $userId, int $addressId): void
    {
        $address = $this->addressRepository->findAddressForUser($userId, $addressId);

        if (! $address) {
            abort(404, 'Alamat tidak ditemukan');
        }

        $this->addressRepository->setAsDefault($address);
    }
}
