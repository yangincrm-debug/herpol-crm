<?php

namespace App\Observers;

use App\Models\Customer;
use Illuminate\Support\Facades\Cache;

class CustomerObserver
{
    public function created(Customer $customer): void
    {
        // Yeni müşteri eklenince cache'i temizle
        Cache::forget('customers_list');
    }

    public function updated(Customer $customer): void
    {
        // Müşteri güncellendiğinde yapılacaklar
    }

    public function deleted(Customer $customer): void
    {
         Cache::forget('customers_list');
    }
}