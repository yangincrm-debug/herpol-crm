<?php

namespace App\Services;

use App\Interfaces\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Exception;

class CustomerService
{
    public function __construct(
        protected CustomerRepositoryInterface $customerRepository
    ) {}

    public function createCustomer(array $data): Customer
    {
        // Örnek İş Mantığı: Vergi numarası varsa Kurumsal yap
        if (!empty($data['tax_number'])) {
            $data['type'] = \App\Enums\CustomerType::CORPORATE;
        }

        // Transaction Güvenliği
        return DB::transaction(function () use ($data) {
            $customer = $this->customerRepository->create($data);
            
            // Burada "Hoşgeldin Maili" atabiliriz veya başka bir servisi çağırabiliriz.
            
            return $customer;
        });
    }

    public function updateCustomer(int $id, array $data): Customer
    {
        $this->customerRepository->update($id, $data);
        return $this->customerRepository->getById($id);
    }
}