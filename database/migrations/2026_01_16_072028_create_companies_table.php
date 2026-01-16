<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // --- Entegrasyon Alanları ---
            // FrmHesapKod: Eşitleme için anahtar alan (Unique)
            $table->string('legacy_code')->unique()->comment('MSSQL FrmHesapKod karşılığı');
            
            // --- Şirket Bilgileri ---
            $table->string('name')->comment('MSSQL FrmUnvan');
            $table->string('account_number')->nullable()->comment('MSSQL FrmHesapNo');
            $table->string('short_name')->nullable()->comment('MSSQL KISATNM');
            $table->string('type')->nullable()->comment('MSSQL FRMTIP');
            
            // --- İletişim & Fatura ---
            $table->text('address')->nullable()->comment('MSSQL Adres');
            $table->string('tax_office')->nullable()->comment('MSSQL VergiD');
            $table->string('tax_number')->nullable()->comment('MSSQL VergiNo');
            $table->string('phone')->nullable()->comment('MSSQL Tel');
            $table->string('fax')->nullable()->comment('MSSQL Fax');
            $table->string('email')->nullable()->comment('MSSQL Mail');
            $table->string('contact_person')->nullable()->comment('MSSQL IlgiliKisi');

            // --- Sistem Alanları ---
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};