<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\CustomerRepositoryInterface;
use App\Repositories\CustomerRepository;
use App\Interfaces\CompanyRepositoryInterface;
use App\Repositories\CompanyRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CompanyRepositoryInterface::class, CompanyRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
    }

    public function boot(): void
    {
        //
    }
}