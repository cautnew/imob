<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the financial dashboard for the authenticated user's company.
     */
    public function index(Request $request): Response
    {
        $company = $request->user()->company;

        $month = $request->string('month')->value() ?: now()->format('Y-m');
        $selectedMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        return Inertia::render('dashboard', [
            'selectedMonth' => $selectedMonth->format('Y-m'),
            'monthOptions' => $this->monthOptions(),
            'summary' => $this->summary($company, $selectedMonth),
            'monthlySeries' => $this->monthlySeries($company, $selectedMonth),
            'categoryBreakdown' => $this->categoryBreakdown($company, $selectedMonth),
            'upcoming' => $this->upcoming($company),
        ]);
    }

    /**
     * @return array{
     *     total_income: string, total_expense: string, balance: string,
     *     pending_count: int, pending_amount: string,
     *     overdue_count: int, overdue_amount: string,
     * }
     */
    private function summary(Company $company, Carbon $month): array
    {
        $base = $company->transactions()->whereBetween('due_date', [
            $month->copy()->startOfMonth(),
            $month->copy()->endOfMonth(),
        ]);

        $totalIncome = (clone $base)
            ->whereHas('transactionCategory', fn ($query) => $query->where('type', TransactionType::Income))
            ->sum('amount');

        $totalExpense = (clone $base)
            ->whereHas('transactionCategory', fn ($query) => $query->where('type', TransactionType::Expense))
            ->sum('amount');

        $pending = (clone $base)->open();
        $overdue = $company->transactions()->overdue();

        return [
            'total_income' => $this->money($totalIncome),
            'total_expense' => $this->money($totalExpense),
            'balance' => $this->money($totalIncome - $totalExpense),
            'pending_count' => (clone $pending)->count(),
            'pending_amount' => $this->money((clone $pending)->sum('amount')),
            'overdue_count' => (clone $overdue)->count(),
            'overdue_amount' => $this->money((clone $overdue)->sum('amount')),
        ];
    }

    /**
     * @return list<array{month: string, income: string, expense: string}>
     */
    private function monthlySeries(Company $company, Carbon $month): array
    {
        $series = [];

        for ($i = 5; $i >= 0; $i--) {
            $reference = $month->copy()->subMonths($i);

            $base = $company->transactions()->whereBetween('due_date', [
                $reference->copy()->startOfMonth(),
                $reference->copy()->endOfMonth(),
            ]);

            $income = (clone $base)
                ->whereHas('transactionCategory', fn ($query) => $query->where('type', TransactionType::Income))
                ->sum('amount');

            $expense = (clone $base)
                ->whereHas('transactionCategory', fn ($query) => $query->where('type', TransactionType::Expense))
                ->sum('amount');

            $series[] = [
                'month' => $reference->format('Y-m'),
                'income' => $this->money($income),
                'expense' => $this->money($expense),
            ];
        }

        return $series;
    }

    /**
     * @return list<array{category: string, amount: string}>
     */
    private function categoryBreakdown(Company $company, Carbon $month): array
    {
        return $company->transactions()
            ->whereBetween('due_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->whereHas('transactionCategory', fn ($query) => $query->where('type', TransactionType::Expense))
            ->with('transactionCategory:id,name')
            ->get()
            ->groupBy(fn ($transaction) => $transaction->transactionCategory->name)
            ->map(fn ($group, $name) => [
                'category' => $name,
                'amount' => $this->money($group->sum('amount')),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, description: string, due_date: string, amount: string, status: string, property: string}>
     */
    private function upcoming(Company $company): array
    {
        return $company->transactions()
            ->with('property:id,title')
            ->where('status', TransactionStatus::Pending)
            ->orderBy('due_date')
            ->limit(8)
            ->get()
            ->map(fn ($transaction) => [
                'id' => $transaction->id,
                'description' => $transaction->description,
                'due_date' => $transaction->due_date->toDateString(),
                'amount' => $this->money($transaction->amount),
                'status' => $transaction->effectiveStatus()->value,
                'property' => $transaction->property->title,
            ])
            ->all();
    }

    /**
     * @return list<string>
     */
    private function monthOptions(): array
    {
        return collect(range(0, 11))
            ->map(fn (int $i): string => now()->subMonths($i)->format('Y-m'))
            ->all();
    }

    /**
     * Format a raw sum/amount as a fixed 2-decimal numeric string, regardless of
     * whether the database driver returned it as an int, float or string.
     */
    private function money(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
