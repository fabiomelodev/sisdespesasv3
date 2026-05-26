<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
    table {
        border: 2px solid rgb(140 140 140);
    }

    th,
    td {
        border: 1px solid rgb(160 160 160);
    }
</style>

<body>
    <div>
        <h2>
            Entrada
        </h2>

        <p>
            {{ \App\Helpers\FormatCurrency::getFormatCurrency($incomesPaidMonthCurrentSum) }}
        </p>

        <hr />

        <h2>
            Saídas realizadas
        </h2>

        <p>
            {{ \App\Helpers\FormatCurrency::getFormatCurrency($expensesPaidMonthCurrentSum) }}
        </p>

        <hr />

        <h2>
            Despesas recorrentes
        </h2>

        <p>
            {{ \App\Helpers\FormatCurrency::getFormatCurrency($recurringTransactionsMonthCurrentSum) }}
        </p>
    </div>

    <hr />

    <div>
        <h2>
            Entradas
        </h2>

        <table>
            <thead>
                <tr>
                    <th>
                        Entrada
                    </th>

                    <th>
                        Data
                    </th>

                    <th>
                        Valor
                    </th>
                </tr>
            </thead>

            <tbody>
                @foreach($incomes as $income)
                    <tr>
                        <td>
                            {{ $income->name }}
                        </td>

                        <td>
                            {{ $income->transaction_date->format('d/m/Y') }}
                        </td>

                        <td>
                            {{ \App\Helpers\FormatCurrency::getFormatCurrency($income->amount) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>

    <hr />

    <div>
        <h2>
            Despesas por categorias
        </h2>

        <table>
            <thead>
                <tr>
                    <th>
                        Categoria
                    </th>

                    <th>
                        Porcentagem
                    </th>

                    <th>
                        Valor
                    </th>
                </tr>
            </thead>

            <tbody>
                @foreach($expensesCategory as $expenseCategory)
                    <tr>
                        <td>
                            {{ $expenseCategory->name }}
                        </td>

                        <td>
                            {{ number_format($expenseCategory->percentage, 0, ',', '.') . '%' }}
                        </td>

                        <td>
                            {{ \App\Helpers\FormatCurrency::getFormatCurrency($expenseCategory->totalExpenses) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>

    <hr />

    <div>
        <h2>
            Despesas
        </h2>

        <table>
            <thead>
                <tr>
                    <th>
                        Despesa
                    </th>

                    <th>
                        Categoria
                    </th>

                    <th>
                        Data
                    </th>

                    <th>
                        Valor
                    </th>
                </tr>
            </thead>

            <tbody>
                @foreach($expenses as $expense)
                    <tr>
                        <td>
                            {{ $expense->name }}
                        </td>

                        <td>
                            {{ $expense->category->name }}
                        </td>

                        <td>
                            {{ $expense->transaction_date->format('d/m/Y') }}
                        </td>

                        <td>
                            {{ \App\Helpers\FormatCurrency::getFormatCurrency($expense->amount) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>

    <hr />

    <div>
        <h2>
            Faturas de cartões de crédito
        </h2>

        <table>
            <thead>
                <tr>
                    <th>
                        Fatura
                    </th>

                    <th>
                        Vencimento
                    </th>

                    <th>
                        Valor
                    </th>
                </tr>
            </thead>

            <tbody>
                @foreach($invoices as $invoice)
                    <tr>
                        <td>
                            {{ $invoice->creditCard->name }}
                        </td>

                        <td>
                            {{ $invoice->due_date->format('d/m/Y') }}
                        </td>

                        <td>
                            {{ \App\Helpers\FormatCurrency::getFormatCurrency($invoice->totalExpenses) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>
</body>

</html>