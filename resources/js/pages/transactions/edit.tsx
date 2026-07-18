import { Head } from '@inertiajs/react';
import TransactionForm from '@/components/finance/transaction-form';
import type { TransactionFormValues } from '@/components/finance/transaction-form';
import Heading from '@/components/heading';
import { index, update } from '@/routes/transactions';
import type { BreadcrumbItem } from '@/types';

type PropertyOption = {
    id: number;
    title: string;
};

type LeaseOption = {
    id: number;
    property: { id: number; title: string } | null;
};

type CategoryOption = {
    id: number;
    name: string;
    type: string;
};

type EditableTransaction = {
    id: number;
    property_id: number;
    lease_id: number | null;
    transaction_category_id: number;
    description: string;
    amount: string;
    due_date: string;
    notes: string | null;
};

type Props = {
    transaction: EditableTransaction;
    properties: PropertyOption[];
    leases: LeaseOption[];
    transactionCategories: CategoryOption[];
};

export default function TransactionsEdit({
    transaction,
    properties,
    leases,
    transactionCategories,
}: Props) {
    const defaultValues: Partial<TransactionFormValues> = {
        property_id: String(transaction.property_id),
        lease_id: transaction.lease_id ? String(transaction.lease_id) : '',
        transaction_category_id: String(transaction.transaction_category_id),
        description: transaction.description,
        amount: transaction.amount,
        due_date: transaction.due_date,
        notes: transaction.notes ?? '',
    };

    return (
        <>
            <Head title={`Editar ${transaction.description}`} />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Editar lançamento"
                    description={`Atualize os dados de ${transaction.description}`}
                />

                <TransactionForm
                    action={update(transaction.id)}
                    properties={properties}
                    leases={leases}
                    transactionCategories={transactionCategories}
                    defaultValues={defaultValues}
                    submitLabel="Salvar alterações"
                    backHref={index().url}
                />
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Financeiro',
        href: index(),
    },
    {
        title: 'Editar lançamento',
        href: '',
    },
];

TransactionsEdit.layout = {
    breadcrumbs,
};
