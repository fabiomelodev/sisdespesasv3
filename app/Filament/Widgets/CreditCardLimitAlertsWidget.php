<?php

namespace App\Filament\Widgets;

use App\Models\CreditCard;
use Filament\Widgets\Widget;

class CreditCardLimitAlertsWidget extends Widget
{
    protected string $view = 'filament.widgets.credit-card-limit-alerts';

    protected int|string|array $columnSpan = 1;

    public function getAlerts(): array
    {
        return CreditCard::query()
            ->where('is_active', true)
            ->get()
            ->map(function (CreditCard $creditCard): array {
                $used = (float) $creditCard->invoices()
                    ->where('invoices.is_paid', false)
                    ->join('transactions', 'transactions.invoice_id', '=', 'invoices.id')
                    ->sum('transactions.amount');

                $percentage = $creditCard->limit > 0
                    ? ($used * 100) / $creditCard->limit
                    : 0;

                return [
                    'name' => $creditCard->name,
                    'used' => $used,
                    'limit' => $creditCard->limit,
                    'percentage' => $percentage,
                ];
            })
            ->filter(fn(array $alert): bool => $alert['percentage'] >= 80)
            ->sortByDesc('percentage')
            ->values()
            ->all();
    }
}
