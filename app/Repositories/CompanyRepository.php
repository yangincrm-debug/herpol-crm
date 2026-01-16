<?php

namespace App\Repositories;

use App\Interfaces\CompanyRepositoryInterface;
use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;

class CompanyRepository implements CompanyRepositoryInterface
{
    public function getAll(): Collection
    {
        return Company::latest()->get();
    }

    public function getById(int $id): ?Company
    {
        return Company::find($id);
    }

    public function getByLegacyCode(string $code): ?Company
    {
        return Company::where('legacy_code', $code)->first();
    }

    public function create(array $data): Company
    {
        return Company::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return Company::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return Company::destroy($id);
    }

    // Entegrasyon (Sync) işlemi için kritik metod
    public function updateOrCreateByLegacyCode(string $code, array $data): Company
    {
        return Company::updateOrCreate(
            ['legacy_code' => $code],
            $data
        );
    }
}