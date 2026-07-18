<?php

namespace Database\Seeders;

use App\Enums\BillEventType;
use App\Enums\BillStatus;
use App\Enums\TransactionStatus;
use App\Models\Bill;
use App\Models\Company;
use App\Models\Lease;
use App\Models\Property;
use App\Models\TransactionCategory;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

/**
 * Provisions the demo company's boletos and lançamentos: a monthly rent
 * billing history (paid, overdue, and open) for every active lease, plus
 * standalone expense entries (IPTU, condomínio, manutenção, seguro) across
 * the whole property portfolio.
 */
class DemoFinancialSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('slug', DemoCompanySeeder::COMPANY_SLUG)->firstOrFail();

        $categories = $company->transactionCategories()->get()->keyBy('name');
        $rentCategory = $categories['Aluguel'];

        foreach ($company->leases()->get() as $lease) {
            foreach ([3, 2, 1] as $monthsAgo) {
                $this->createRentBill($company, $lease, $rentCategory, now()->subMonths($monthsAgo), paid: true);
            }

            $this->createRentBill($company, $lease, $rentCategory, now()->subDays(fake()->numberBetween(5, 20)), paid: false);
            $this->createRentBill($company, $lease, $rentCategory, now()->addDays(fake()->numberBetween(3, 25)), paid: false);
        }

        $expenseCategories = $company->transactionCategories()
            ->whereIn('name', ['IPTU', 'Condomínio', 'Manutenção', 'Seguro'])
            ->get();

        $this->seedPropertyExpenses($company, $expenseCategories);
    }

    private function createRentBill(Company $company, Lease $lease, TransactionCategory $rentCategory, CarbonInterface $dueDate, bool $paid): void
    {
        $status = $paid ? BillStatus::Paid : BillStatus::Pending;
        $paidDate = $paid ? $dueDate->copy() : null;
        $reference = $dueDate->copy()->locale('pt_BR')->translatedFormat('F/Y');

        $bill = $lease->bills()->create([
            'company_id' => $company->id,
            'due_date' => $dueDate,
            'paid_date' => $paidDate,
            'status' => $status,
            'description' => "Aluguel referente a {$reference}",
        ]);

        $bill->events()->create([
            'type' => BillEventType::Created,
            'occurred_on' => $dueDate,
            'description' => 'Boleto criado.',
        ]);

        $lease->transactions()->create([
            'company_id' => $company->id,
            'property_id' => $lease->property_id,
            'bill_id' => $bill->id,
            'transaction_category_id' => $rentCategory->id,
            'description' => "Aluguel - {$reference}",
            'amount' => $lease->rent_amount,
            'due_date' => $dueDate,
            'paid_date' => $paidDate,
            'status' => $paid ? TransactionStatus::Paid : TransactionStatus::Pending,
        ]);

        if ($paid) {
            $this->markBillAsPaid($bill);
        }
    }

    private function markBillAsPaid(Bill $bill): void
    {
        $bill->events()->create([
            'type' => BillEventType::MarkedAsPaid,
            'occurred_on' => $bill->paid_date,
            'description' => 'Boleto marcado como pago.',
        ]);
    }

    /**
     * @param  Collection<int, TransactionCategory>  $expenseCategories
     */
    private function seedPropertyExpenses(Company $company, Collection $expenseCategories): void
    {
        foreach ($company->properties()->get() as $property) {
            $categoriesForProperty = fake()->randomElements($expenseCategories->all(), fake()->numberBetween(1, 3));

            foreach ($categoriesForProperty as $category) {
                $this->createExpenseTransaction($company, $property, $category);
            }
        }
    }

    private function createExpenseTransaction(Company $company, Property $property, TransactionCategory $category): void
    {
        $paid = fake()->boolean(60);
        $dueDate = $paid ? now()->subDays(fake()->numberBetween(10, 90)) : now()->addDays(fake()->numberBetween(-15, 30));

        $company->transactions()->create([
            'property_id' => $property->id,
            'transaction_category_id' => $category->id,
            'description' => "{$category->name} - {$property->title}",
            'amount' => fake()->randomFloat(2, 80, 1200),
            'due_date' => $dueDate,
            'paid_date' => $paid ? $dueDate->copy() : null,
            'status' => $paid ? TransactionStatus::Paid : TransactionStatus::Pending,
        ]);
    }
}
