<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $guarded = ['id'];

    public function scopeIsExpense(Builder $query): Builder
    {
        return $query->where('type', 'expense');
    }

    public function scopeIsIncome(Builder $query): Builder
    {
        return $query->where('type', 'income');
    }

    protected function percentage(): Attribute
    {
        return Attribute::make(
            get: function (): float|int {
                $expensesByCategorySum = $this->transactions()->monthCurrent()->isPaid()->typeExpense()->sum('amount');

                $incomesSum = Transaction::monthCurrent()->isPaid()->typeIncome()->sum('amount');

                $result = ($expensesByCategorySum / $incomesSum) * 100;

                return $result;
            }
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
