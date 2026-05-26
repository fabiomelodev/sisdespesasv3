<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function show()
    {
        $incomesPaidMonthCurrentSum = Transaction::query()
            ->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->isIncome()
            ->isPaid()
            ->sum('amount');

        $expensesPaidMonthCurrentSum = Transaction::query()
            ->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->isExpense()
            ->whereIn('payment_method', ['debit', 'pix'])
            ->whereNull('recurring_transaction_id')
            ->isPaid()
            ->sum('amount');

        $recurringTransactionsMonthCurrentSum = Transaction::query()
            ->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->isExpense()
            ->where('payment_method', '!=', 'credit')
            ->whereHas('recurringTransaction', function (Builder $query) {
                $query->isActive();
            })
            ->sum('amount');

        $incomes = Transaction::query()->whereYear('transaction_date', now()->year)->whereMonth('transaction_date', now()->month)->isIncome()->isPaid()->get();

        $expenses = Transaction::query()->orderBy('transaction_date', 'desc')->whereYear('transaction_date', now()->year)->whereMonth('transaction_date', now()->month)->isExpense()->isPaid()->get();

        $expensesCategory = Category::query()
            ->orderBy('name', 'asc')
            ->select('categories.*')
            ->selectSub(function ($query) {
                $query->from('transactions')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('transactions.category_id', 'categories.id')
                    // ->where('is_paid', 1)
                    ->where('type', 'expense')
                    ->whereYear('transaction_date', now()->year)
                    ->whereMonth('transaction_date', now()->month);
            }, 'totalExpenses')
            ->having('totalExpenses', '>', 0)
            ->get();

        $invoices = Invoice::query()
            ->select('invoices.*')
            // Aplicando o filtro de data dinâmico
            ->whereYear('due_date', now()->year)
            ->whereMonth('due_date', now()->month)
            ->selectSub(function ($query) {
                $query->from('transactions')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('transactions.invoice_id', 'invoices.id')
                    ->where('type', 'expense');
            }, 'totalExpenses')
            ->having('totalExpenses', '>', 0)
            ->orderBy('due_date', 'asc')
            ->get();


        return view('pages.report', [
            'incomesPaidMonthCurrentSum' => $incomesPaidMonthCurrentSum,
            'expensesPaidMonthCurrentSum' => $expensesPaidMonthCurrentSum,
            'recurringTransactionsMonthCurrentSum' => $recurringTransactionsMonthCurrentSum,
            'incomes' => $incomes,
            'expensesCategory' => $expensesCategory,
            'expenses' => $expenses,
            'invoices' => $invoices
        ]);
    }
}
