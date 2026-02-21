<?php

namespace App\Console\Commands;

use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunRecurringTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-recurring-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera transações a partir de despesas/receitas recorrentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $firstDayOfMonth = Carbon::now()->startOfMonth();

        $recurrings = RecurringTransaction::isActive()
            ->whereDate('next_run_date', $firstDayOfMonth)
            ->get();

        if ($recurrings->isEmpty()) {
            $this->info('Nenhuma recorrência para executar.');
            return Command::SUCCESS;
        }

        foreach ($recurrings as $recurring) {
            DB::transaction(function () use ($recurring) {
                $this->createTransaction($recurring);
                $this->updateNextRunDate($recurring);
            });
        }

        $this->info("{$recurrings->count()} recorrência(s) executada(s).");

        return Command::SUCCESS;
    }

    protected function createTransaction(RecurringTransaction $recurring)
    {
        $transaction_date = Carbon::now()
            ->startOfMonth()
            ->addDays($recurring->due_day - 1);

        Transaction::create([
            'name' => $recurring->name,
            'type' => $recurring->type,
            'amount' => $recurring->amount,
            'payment_method' => $recurring->payment_method,
            'transaction_date' => $transaction_date,
            'is_paid' => 0,
            'account_id' => $recurring->account_id,
            'category_id' => $recurring->category_id,
            'credit_card_id' => $recurring->credit_card_id,
            'recurring_transaction_id' => $recurring->id
        ]);
    }

    protected function updateNextRunDate(RecurringTransaction $recurring)
    {
        $date = $recurring->next_run_date->copy();

        $next = $date->addMonthsNoOverflow(1);

        $recurring->update([
            'next_run_date' => $next
        ]);
    }
}
