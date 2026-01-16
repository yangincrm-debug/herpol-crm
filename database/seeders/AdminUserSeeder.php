<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Filament Shield config dosyasından "Super Admin" rol adını al
        // Varsayılan olarak 'super_admin'dir ama config'den değiştirilmiş olabilir.
        $superAdminRoleName = config('filament-shield.super_admin.name', 'super_admin');

        // 2. Rolü oluştur (Eğer yoksa)
        // Guard name 'web' olarak belirtilmeli çünkü Filament varsayılan olarak web guard kullanır.
        $role = Role::firstOrCreate(
            ['name' => $superAdminRoleName, 'guard_name' => 'web']
        );

        // 3. Kullanıcıyı oluştur veya bul (firstOrCreate)
        $user = User::firstOrCreate(
            ['email' => 'admin@herpol.com'], // Bu e-posta varsa tekrar oluşturma
            [
                'name' => 'Herpol Root Admin',
                'password' => Hash::make('password'), // Prod ortamında mutlaka değiştirilmeli!
                'email_verified_at' => now(),
            ]
        );

        // 4. Rolü kullanıcıya ata
        if (! $user->hasRole($superAdminRoleName)) {
            $user->assignRole($role);
            $this->command->info("Başarılı: '{$superAdminRoleName}' rolü admin kullanıcısına atandı.");
        } else {
            $this->command->warn("Bilgi: Kullanıcı zaten '{$superAdminRoleName}' yetkisine sahip.");
        }

        $this->command->info("--------------------------------------------------");
        $this->command->info("Admin Giriş Bilgileri:");
        $this->command->info("Email: admin@herpol.com");
        $this->command->info("Şifre: password");
        $this->command->info("--------------------------------------------------");
    }
}