<?php

namespace App\Repositories;

use App\Interfaces\CustomerRepositoryInterface;
use App\Models\Customer;
use App\Enums\CustomerStatus;
use Illuminate\Database\Eloquent\Collection;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function getAll(): Collection
    {
        return Customer::latest()->get();
    }

    public function getById(int $id): ?Customer
    {
        return Customer::find($id);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return Customer::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return Customer::destroy($id);
    }

    public function getLeads(): Collection
    {
        return Customer::where('status', CustomerStatus::LEAD)->get();
    }
}