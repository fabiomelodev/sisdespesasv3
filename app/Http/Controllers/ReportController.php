<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Invoice;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function show()
    {
        $month = request()->query('month', now()->month);

        $year = request()->query('year', now()->year);

        $applyDateFilter = function ($query, string $column = 'transaction_date') use ($month, $year) {
            return $query
                ->when($year, fn($q) => $q->whereYear($column, $year))
                ->when($month, fn($q) => $q->whereMonth($column, $month))
                ->when(!$year && !$month, fn($q) => $q->whereYear($column, now()->year)->whereMonth($column, now()->month));
        };

        $incomesPaidMonthCurrentSum = Transaction::query()
            ->tap(fn(Builder $query): Builder => $applyDateFilter($query, 'transaction_date'))
            ->isIncome()
            ->isPaid()
            ->sum('amount');

        $expensesPaidMonthCurrentSum = Transaction::query()
            ->tap(fn(Builder $query): Builder => $applyDateFilter($query, 'transaction_date'))
            ->isExpense()
            ->whereIn('payment_method', ['debit', 'pix'])
            ->whereNull('recurring_transaction_id')
            ->isPaid()
            ->sum('amount');

        $recurringTransactionsMonthCurrentSum = Transaction::query()
            ->tap(fn(Builder $query): Builder => $applyDateFilter($query, 'transaction_date'))
            ->isExpense()
            ->where('payment_method', '!=', 'credit')
            ->whereHas('recurringTransaction', function (Builder $query) {
                $query->isActive();
            })
            ->sum('amount');

        $incomes = Transaction::query()->tap(fn(Builder $query): Builder => $applyDateFilter($query, 'transaction_date'))->isIncome()->isPaid()->get();

        $expenses = Transaction::query()->tap(fn(Builder $query): Builder => $applyDateFilter($query, 'transaction_date'))->orderBy('transaction_date', 'desc')->isExpense()->isPaid()->get();

        $expensesCategory = Category::query()
            ->orderBy('name', 'asc')
            ->select('categories.*')
            ->selectSub(function ($query) use ($applyDateFilter) {
                $query->from('transactions')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('transactions.category_id', 'categories.id')
                    // ->where('is_paid', 1)
                    ->where('type', 'expense')
                    ->tap(fn($query) => $applyDateFilter($query, 'transaction_date'));
            }, 'totalExpenses')
            ->having('totalExpenses', '>', 0)
            ->get();

        $targetDate = Carbon::createFromDate($year, $month, 1)->addMonthNoOverflow();

        $invoices = Invoice::query()
            ->select('invoices.*')
            ->whereMonth('due_date', $targetDate->month)
            ->whereYear('due_date', $targetDate->year)
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
