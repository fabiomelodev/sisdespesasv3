<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Reservation extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            if ($model->is_active) {
                $model->reservationTransactions()->each(function ($reservationTransaction) {
                    $reservationTransaction->is_paid = 1;

                    $reservationTransaction->save();
                });
            } else {
                $model->reservationTransactions()->each(function ($reservationTransaction) {
                    $reservationTransaction->is_paid = 0;

                    $reservationTransaction->save();
                });
            }
        });
    }

    public function scopeIsActive(Builder $query): Builder
    {
        return $query->where('is_active', 1);
    }

    // protected function currentAmount(): Attribute
    // {
    //     return Attribute::make(
    //         get: function (): int|string {
    //             if ($this->reservationTransactions()) {
    //                 return $this->reservationTransactions()->isPaid()->sum('amount');
    //             }

    //             return 0;
    //         }
    //     );
    // }

    protected function percentage(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $reservationTransactionsSum = $this->reservationTransactions()->isPaid()->sum('amount');

                $result = ($reservationTransactionsSum / $this->target_amount) * 100;

                return (int) $result;
            }
        );
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function reservationTransactions(): HasMany
    {
        return $this->hasMany(ReservationTransaction::class);
    }
}
