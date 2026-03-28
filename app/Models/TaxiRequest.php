<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxiRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'pickup_address',
        'status',
        'scheduled_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
