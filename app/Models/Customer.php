<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Customer extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'company_id',
        'first_name',
        'last_name',
        'title',
        'email',
        'phone',
        'address',
        'type',
        'status',
        'notes',
    ];

    // --- Enum Casting (Unutulan Kısım) ---
    protected $casts = [
        'type' => CustomerType::class,
        'status' => CustomerStatus::class,
    ];

    // --- İlişkiler ---
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // --- Accessors (Kolaylıklar) ---
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}",
        );
    }

    // --- Activity Log Ayarları ---
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'email', 'status', 'company_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Müşteri {$eventName}");
    }
}