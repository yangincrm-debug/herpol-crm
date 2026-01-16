<?php

namespace App\Services;

use App\Interfaces\CompanyRepositoryInterface;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Exception;

class CompanyService
{
    public function __construct(
        protected CompanyRepositoryInterface $companyRepository
    ) {}

    public function createCompany(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            // İş kuralı: Eğer Legacy Code yoksa, manuel ekleniyorsa sistemsel bir kod üretilebilir
            // if (empty($data['legacy_code'])) { ... }
            
            return $this->companyRepository->create($data);
        });
    }

    public function updateCompany(int $id, array $data): Company
    {
        $this->companyRepository->update($id, $data);
        return $this->companyRepository->getById($id);
    }

    /**
     * Entegrasyon servisinden çağrılacak metod.
     * MSSQL verisini alır ve sistemi günceller.
     */
    public function syncFromLegacy(array $legacyData): Company
    {
        // Gelen veriyi map'le
        $mappedData = [
            'name'           => $legacyData['FrmUnvan'],
            'account_number' => $legacyData['FrmHesapNo'] ?? null,
            'short_name'     => $legacyData['KISATNM'] ?? null,
            'type'           => $legacyData['FRMTIP'] ?? null,
            'address'        => $legacyData['Adres'] ?? null,
            'tax_office'     => $legacyData['VergiD'] ?? null,
            'tax_number'     => $legacyData['VergiNo'] ?? null,
            'phone'          => $legacyData['Tel'] ?? null,
            'fax'            => $legacyData['Fax'] ?? null,
            'email'          => $legacyData['Mail'] ?? null,
            'contact_person' => $legacyData['IlgiliKisi'] ?? null,
        ];

        return $this->companyRepository->updateOrCreateByLegacyCode(
            $legacyData['FrmHesapKod'], 
            $mappedData
        );
    }
}