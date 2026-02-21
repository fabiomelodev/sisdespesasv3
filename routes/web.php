<?php

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('expense', function () {
//     \App\Models\Deposit::query()->with('bank')->whereHas('bank', function (Builder $query): Builder {
//         return $query->where('title', 'Inter');
//     })->each(function ($deposit) {
//         $bank = $deposit->bank()->first()->title;

//         $account = \App\Models\Account::where('name', $bank)->first();

//         $category = \App\Models\Category::find(30);

//         $category_id = 30;

//         \App\Models\Transaction::create([
//             'name' => $deposit->type,
//             'type' => 'income',
//             'amount' => $deposit->wage,
//             'payment_method' => 'debit',
//             'transaction_date' => $deposit->entry_date,
//             'is_paid' => $deposit->status == 'pago' ? 1 : 0,
//             'account_id' => $account->id,
//             'category_id' => $category_id,
//         ]);
//     });
// });