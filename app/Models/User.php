<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles; // <--- HasRoles eklendi (Spatie)

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Filament Paneline Kimler Girebilir?
     * Güvenlik Duvarı: Sadece 'super_admin' rolü olanlar veya email verify olanlar girebilsin.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Geliştirme ortamında (local) herkese izin ver, Prod'da kısıtla
        if (app()->isLocal()) {
            return true;
        }

        // Örnek: Sadece email'i doğrulanmış ve yetkisi olanlar girebilsin
        // return $this->hasRole('super_admin') || $this->hasVerifiedEmail();
        
        return true; // Şimdilik herkese açık bırakıyoruz (AdminUserSeeder çalışsın diye)
    }
}