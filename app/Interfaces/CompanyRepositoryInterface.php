<?php

namespace App\Interfaces;

use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;

interface CompanyRepositoryInterface
{
    public function getAll(): Collection;
    public function getById(int $id): ?Company;
    public function getByLegacyCode(string $code): ?Company; // Yeni Metod
    public function create(array $data): Company;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function updateOrCreateByLegacyCode(string $code, array $data): Company; // Sync İçin
}