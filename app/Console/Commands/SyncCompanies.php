<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

class SyncCompanies extends Command
{
    protected $signature = 'sync:companies';
    protected $description = 'MSSQL TnmFirma tablosundan yeni şirketleri sisteme çeker.';

    public function handle()
    {
        $this->info('🚀 MSSQL Senkronizasyonu başlatılıyor...');
        $startTime = microtime(true);

        try {
            // 1. Bağlantı Kontrolü
            $remoteCount = DB::connection('sqlsrv')->table('dbo.TnmFirma')->count();
            $this->info("📡 Bağlantı Başarılı. Kaynak Tablo: {$remoteCount} kayıt.");

            $addedCount = 0;
            $skippedCount = 0;

            // 2. Verileri Parça Parça Çek
            // DÜZELTME: chunk() kullanırken MUTLAKA orderBy() gereklidir.
            DB::connection('sqlsrv')
                ->table('dbo.TnmFirma')
                ->select([
                    'FrmHesapKod', 'FrmHesapNo', 'FrmUnvan', 'Adres', 
                    'VergiD', 'VergiNo', 'Tel', 'Fax', 'Mail', 
                    'IlgiliKisi', 'FRMTIP', 'KISATNM'
                ])
                ->orderBy('FrmHesapKod') // <-- KRİTİK EKLEME BURASI
                ->chunk(500, function ($rows) use (&$addedCount, &$skippedCount) {
                    
                    // Bu paketteki kodları al
                    $remoteCodes = $rows->pluck('FrmHesapKod')->map(fn($item) => trim($item))->toArray();
                    
                    // Bizim veritabanımızda zaten var olanları bul
                    $existingCodes = Company::whereIn('legacy_code', $remoteCodes)
                                            ->pluck('legacy_code')
                                            ->toArray();

                    $insertData = [];
                    $now = now();

                    foreach ($rows as $row) {
                        $code = trim($row->FrmHesapKod);

                        // Varsa atla
                        if (in_array($code, $existingCodes)) {
                            $skippedCount++;
                            continue;
                        }

                        // Yoksa ekleme listesine al
                        $insertData[] = [
                            'legacy_code'    => $code,
                            'name'           => trim($row->FrmUnvan) ?: 'İsimsiz Firma',
                            'account_number' => trim($row->FrmHesapNo),
                            'short_name'     => trim($row->KISATNM),
                            'type'           => trim($row->FRMTIP),
                            'address'        => trim($row->Adres),
                            'tax_office'     => trim($row->VergiD),
                            'tax_number'     => trim($row->VergiNo),
                            'phone'          => trim($row->Tel),
                            'fax'            => trim($row->Fax),
                            'email'          => trim($row->Mail),
                            'contact_person' => trim($row->IlgiliKisi),
                            'created_at'     => $now,
                            'updated_at'     => $now,
                        ];
                        
                        $addedCount++;
                    }

                    // Toplu Kayıt
                    if (!empty($insertData)) {
                        Company::insert($insertData);
                    }
                    
                    $this->comment("... işleniyor (Eklenen: {$addedCount})");
                });

            $duration = round(microtime(true) - $startTime, 2);
            $this->newLine();
            $this->table(['Durum', 'Sayı'], [
                ['Yeni Eklenen', $addedCount],
                ['Atlanan (Mevcut)', $skippedCount],
                ['İşlem Süresi', "{$duration} saniye"],
            ]);
            $this->info("✅ İŞLEM TAMAMLANDI!");

        } catch (\Exception $e) {
            $this->error('❌ HATA: ' . $e->getMessage());
            \Log::error('MSSQL Sync Hatası: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}