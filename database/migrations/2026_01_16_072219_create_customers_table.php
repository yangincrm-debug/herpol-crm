<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\CustomerType;
use App\Enums\CustomerStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            
            // İlişki: Bir kişi bir şirkete bağlı olabilir veya olmayabilir (Bireysel)
            $table->foreignId('company_id')
                  ->nullable()
                  ->constrained('companies')
                  ->nullOnDelete();

            // Kişi Bilgileri
            $table->string('first_name');
            $table->string('last_name');
            $table->string('title')->nullable(); // Unvan (Müdür, Muhasebeci vb.)
            
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            
            // Eğer Bireyselse adresi burada tutabiliriz
            $table->text('address')->nullable();

            // Enum Alanları (Varsayılan değerleriyle)
            $table->string('type')->default(CustomerType::INDIVIDUAL->value);
            $table->string('status')->default(CustomerStatus::LEAD->value);
            
            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};