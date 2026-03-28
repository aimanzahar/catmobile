<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'start_time',
        'end_time',
        'is_available',
        'max_bookings',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_available' => 'boolean',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
