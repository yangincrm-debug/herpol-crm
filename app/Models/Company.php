<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Company extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'legacy_code', // Entegrasyon ID
        'name',
        'account_number',
        'short_name',
        'type',
        'address',
        'tax_office',
        'tax_number',
        'phone',
        'fax',
        'email',
        'contact_person',
        // 'logo_path' kaldırdık, Spatie Media Library kullanacağız.
    ];

    // --- İlişkiler ---
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    // --- Activity Log ---
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'legacy_code', 'name', 'account_number', 'tax_number', 'status'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Şirket {$eventName} (Kod: {$this->legacy_code})");
    }
}