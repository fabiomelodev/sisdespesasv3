<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $invoices = Invoice::where('is_closed', 0)
            ->whereDate('closing_day', now())
            ->get();

        if ($invoices->isEmpty()) {
            $this->info('Nenhuma fatura para executar.');
            return Command::SUCCESS;
        }

        foreach ($invoices as $invoice) {
            DB::transaction(function () use ($invoice) {
                $this->updateInvoice($invoice);
            });
        }
    }

    protected function updateInvoice(Invoice $invoice)
    {
        $invoice->update([
            'is_closed' => 1
        ]);
    }
}
