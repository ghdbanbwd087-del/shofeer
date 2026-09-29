<?php

namespace App\Models;

use App\Enums\PackageStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Package extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'user_id',
        'tracking_code',
        'description',
        'category',
        'weight_kg',
        'size',
        'image_paths',
        'sender_name',
        'sender_phone',
        'sender_whatsapp',
        'sender_city',
        'recipient_name',
        'recipient_phone',
        'recipient_city',
        'from_city',
        'to_city',
        'requested_date',
        'assigned_driver_id',
        'trip_id',
        'status',
        'cancel_reason',
        'assigned_at',
        'in_transit_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected $hidden = [
        'sender_phone',
        'sender_whatsapp',
        'recipient_phone',
        'image_paths',
    ];

    protected function casts(): array
    {
        return [
            'weight_kg' => 'decimal:2',
            'image_paths' => 'array',
            'requested_date' => 'date',
            'status' => PackageStatus::class,
            'assigned_at' => 'datetime',
            'in_transit_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(
            Driver::class,
            'assigned_driver_id'
        );
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(
            Trip::class
        );
    }
}
