<?php

namespace App\Interfaces;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

interface CustomerRepositoryInterface
{
    public function getAll(): Collection;
    public function getById(int $id): ?Customer;
    public function create(array $data): Customer;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getLeads(): Collection; // Özel bir metod örneği
}